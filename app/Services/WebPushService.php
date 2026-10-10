<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class WebPushService
{
    public function sendToUser(Akun $akun, string $title, string $body, string $url = '/dashboard'): int
    {
        if (!$akun->isPeminjam()) {
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
                        'url' => $this->safeUrl($url),
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

    private function safeUrl(string $url): string
    {
        $parts = parse_url($url);
        $path = $parts['path'] ?? '';
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return '/dashboard';
        }

        if (isset($parts['host'])) {
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            if (!$appHost || strcasecmp($parts['host'], $appHost) !== 0) {
                return '/dashboard';
            }
        }

        return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }
}
