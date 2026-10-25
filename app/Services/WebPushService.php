<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

/**
 * Service WebPushService
 * 
 * Mengelola pengiriman Web Push Notification standar VAPID ke browser perangkat pengguna.
 * Mendukung pengiriman ke:
 * - Peminjam (Siswa & Pegawai) untuk status persetujuan / pengembalian.
 * - Admin Sarana untuk informasi permohonan baru dan pembatalan real-time.
 */
class WebPushService
{
    /**
     * Mengirim notifikasi web push ke seluruh langganan browser aktif milik akun pengguna.
     *
     * @param  Akun    $akun   Model akun penerima notifikasi
     * @param  string  $title  Judul notifikasi
     * @param  string  $body   Isi pesan notifikasi
     * @param  string  $url    URL target saat notifikasi diklik
     * @return int             Jumlah notifikasi yang berhasil terkirim
     */
    public function sendToUser(Akun $akun, string $title, string $body, string $url = '/dashboard'): int
    {
        // Validasi: hanya peminjam atau admin sarana yang didukung menerima Web Push
        if (!$akun->isPeminjam() && !$akun->isAdminSarana()) {
            return 0;
        }

        $publicKey = config('services.webpush.public_key');
        $privateKey = config('services.webpush.private_key');
        $subject = config('services.webpush.subject');

        if (!$publicKey || !$privateKey || !$subject) {
            Log::notice('Web Push dilewati karena konfigurasi VAPID belum lengkap.');
            return 0;
        }

        $subscriptions = PushSubscription::where('id_akun', $akun->id_akun)->get();
        if ($subscriptions->isEmpty()) {
            return 0;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => $subject,
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ], [
                'TTL' => 300,
                'urgency' => 'normal',
            ]);

            foreach ($subscriptions as $storedSubscription) {
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $storedSubscription->endpoint,
                        'keys' => [
                            'p256dh' => $storedSubscription->public_key,
                            'auth' => $storedSubscription->auth_token,
                        ],
                        ...($storedSubscription->content_encoding ? ['contentEncoding' => $storedSubscription->content_encoding] : []),
                    ]),
                    json_encode([
                        'title' => $title,
                        'body' => $body,
                        'url' => $this->safeUrl($url, $akun),
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                );
            }

            $sent = 0;
            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    $sent++;
                    continue;
                }

                $endpoint = $report->getRequest()->getUri()->__toString();
                $responseCode = $report->getResponse()?->getStatusCode();
                if (in_array($responseCode, [404, 410], true)) {
                    PushSubscription::where('endpoint_hash', hash('sha256', $endpoint))->delete();
                    continue;
                }

                Log::warning('Pengiriman Web Push gagal.', [
                    'id_akun' => $akun->id_akun,
                    'status' => $responseCode,
                    'reason' => $report->getReason(),
                ]);
            }

            return $sent;
        } catch (Throwable $exception) {
            Log::warning('Web Push gagal diproses.', [
                'id_akun' => $akun->id_akun,
                'message' => $exception->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Memvalidasi dan membersihkan URL target notifikasi push agar aman dari open-redirect.
     * Mengarahkan ke fallback spesifik per role (admin verifications atau dashboard siswa).
     */
    private function safeUrl(string $url, ?Akun $akun = null): string
    {
        $defaultFallback = ($akun && $akun->isAdminSarana()) ? '/admin/verifications' : '/dashboard';
        $parts = parse_url($url);
        $path = $parts['path'] ?? '';
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return $defaultFallback;
        }

        if (isset($parts['host'])) {
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            if (!$appHost || strcasecmp($parts['host'], $appHost) !== 0) {
                return $defaultFallback;
            }
        }

        return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }
}
