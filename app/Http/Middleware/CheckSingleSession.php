<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: CheckSingleSession (Single Session Lock)
 * 
 * Memastikan bahwa satu akun pengguna hanya dapat aktif pada 1 perangkat / peramban dalam satu waktu.
 * Jika akun yang sama berhasil login pada perangkat baru (Perangkat B), maka nilai `current_session_id`
 * di database akan berganti. Saat perangkat lama (Perangkat A) mengirim request berikutnya,
 * ID sesinya tidak lagi cocok, sehingga sesi lama otomatis dimatikan dan diarahkan kembali ke halaman login.
 */
class CheckSingleSession
{
    /**
     * Menangani request masuk.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Abaikan pengecekan jika rute yang dituju adalah logout agar tidak terjadi loop
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        // Hanya periksa jika pengguna sedang terotentikasi dan memiliki session aktif
        if (Auth::check() && $request->hasSession()) {
            $user = Auth::user();
            $currentSessionId = $request->session()->getId();

            // Jika di database sudah ada record session ID dan tidak cocok dengan sesi browser ini:
            if (!empty($user->current_session_id) && $user->current_session_id !== $currentSessionId) {
                // Logout sesi pengguna saat ini di peramban lama
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $pesanPeringatan = 'Sesi Anda telah berakhir karena akun ini baru saja login di perangkat atau peramban lain demi keamanan.';

                // Tangani respons jika request berupa AJAX / JSON API (misal realtime polling atau fetch)
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $pesanPeringatan,
                        'redirect' => route('login'),
                    ], 401);
                }

                // Redirect ke halaman login dengan flash message peringatan yang informatif
                return redirect()->route('login')->with('warning', $pesanPeringatan);
            }
        }

        return $next($request);
    }
}
