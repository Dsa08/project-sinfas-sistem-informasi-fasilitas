<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
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

        return response()->json(['message' => 'Notifikasi push berhasil diaktifkan.']);
    }

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

    public function test(Request $request, WebPushService $webPush): JsonResponse
    {
        $sent = $webPush->sendToStudent(
            $request->user(),
            'Uji coba notifikasi SINFAS',
            'Notifikasi push SINFAS berhasil diterima di perangkat ini.',
            '/dashboard'
        );

        if ($sent === 0) {
            return response()->json([
                'message' => 'Notifikasi belum terkirim. Periksa izin browser dan konfigurasi VAPID server.',
            ], 422);
        }

        return response()->json(['message' => 'Notifikasi uji coba dikirim.']);
    }
}
