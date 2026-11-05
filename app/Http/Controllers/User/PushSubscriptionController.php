<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller PushSubscriptionController
 *
 * Mengelola pendaftaran, pembatalan, dan pengujian langganan Web Push Notifications (PWA)
 * dari browser pengguna (Siswa, Pegawai, atau Admin Sarana).
 * Menyimpan data endpoint unik browser dan kunci enkripsi VAPID (`p256dh` & `auth`).
 */
class PushSubscriptionController extends Controller
{
    /**
     * Mendaftarkan atau memperbarui endpoint langganan notifikasi push browser ke database.
     * Menggunakan hashing SHA-256 pada URL endpoint untuk pengindeksan yang efisien dan unik.
     *
     * Prasyarat:
     * - Pengguna telah memberikan izin notifikasi (permission = 'granted') pada browser.
     *
     * Efek Samping:
     * - Menyimpan atau memperbarui data langganan di tabel `push_subscriptions`.
     *
     * @param Request $request Request HTTP yang memuat endpoint URL dan cryptographic keys browser.
     * @return JsonResponse Respon JSON status keberhasilan pendaftaran langganan.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:2048'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'contentEncoding' => ['nullable', 'string', 'max:30'],
        ]);

        $endpointHash = hash('sha256', $data['endpoint']);
        PushSubscription::updateOrCreate(
            ['endpoint_hash' => $endpointHash],
            [
                'id_akun' => $request->user()->id_akun,
                'endpoint' => $data['endpoint'],
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => $data['contentEncoding'] ?? null,
            ]
        );

        return response()->json(['message' => 'Notifikasi berhasil diaktifkan di perangkat ini.']);
    }

    /**
     * Menghapus endpoint langganan push browser saat pengguna menonaktifkan notifikasi di perangkat ini.
     *
     * Efek Samping:
     * - Menghapus baris langganan terkait dari tabel `push_subscriptions`.
     *
     * @param Request $request Request HTTP yang memuat URL endpoint yang akan dihapus.
     * @return JsonResponse Respon JSON status penghapusan langganan.
     */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:2048'],
        ]);

        PushSubscription::where('id_akun', $request->user()->id_akun)
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))
            ->delete();

        return response()->json(['message' => 'Langganan notifikasi dihapus.']);
    }

    /**
     * Mengirimkan notifikasi uji coba langsung ke browser perangkat pengguna untuk memastikan sistem push berfungsi.
     *
     * Efek Samping:
     * - Mengirimkan payload Web Push via koneksi aman VAPID ke push service browser (FCM/Mozilla/Apple).
     *
     * @param Request $request Request HTTP dari pengguna yang meminta uji notifikasi.
     * @param WebPushService $webPush Layanan pengiriman Web Push notification.
     * @return JsonResponse Respon JSON hasil pengiriman uji coba notifikasi.
     */
    public function test(Request $request, WebPushService $webPush): JsonResponse
    {
        $sent = $webPush->sendToUser(
            $request->user(),
            'Uji coba notifikasi SINFAS',
            'Notifikasi push SINFAS berhasil diterima di perangkat ini.',
            '/dashboard'
        );

        if ($sent === 0) {
            return response()->json([
                'message' => 'Notifikasi belum dapat dikirim. Periksa izin notifikasi di perangkat Anda, lalu coba lagi.',
            ], 422);
        }

        return response()->json(['message' => 'Notifikasi uji coba dikirim.']);
    }
}
