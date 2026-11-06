<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware CheckSingleSession
 *
 * Menerapkan kebijakan keamanan Single Session Lock (Satu Akun Hanya Aktif Pada Satu Perangkat).
 * Bekerja memanfaatkan Cache Engine Laravel (`storage/framework/cache`) sehingga sama sekali
 * tidak memerlukan penambahan kolom baru pada skema basis data `akun` (0% perubahan ERD).
 *
 * Mekanisme Kerja:
 * 1. Setiap pengguna yang berhasil login akan dicatat ID sesinya ke dalam memory/cache server.
 * 2. Saat akun yang sama login pada perangkat/peramban baru (Perangkat B), cache ditimpa dengan ID sesi baru.
 * 3. Ketika perangkat lama (Perangkat A) mengirim request berikutnya, ID sesinya tidak lagi cocok dengan cache.
 * 4. Sistem otomatis mengakhiri sesi lama pada Perangkat A dan mengarahkannya kembali ke halaman login.
 */
class CheckSingleSession
{
    /**
     * Menangani verifikasi kecocokan sesi aktif pada setiap request web masuk.
     *
     * Prasyarat:
     * - Pengguna telah terotentikasi melalui middleware `auth` dan memiliki sesi peramban aktif.
     * - Rute `logout` dikecualikan secara eksplisit untuk mencegah loop pengalihan halaman.
     *
     * Efek Samping:
     * - Jika sesi tidak cocok, mengeluarkan pengguna dari sistem (`Auth::logout`), membatalkan sesi lama,
     *   dan mengarahkan kembali ke formulir login disertai pesan peringatan flash.
     *
     * @param Request $request Request HTTP yang sedang diproses.
     * @param Closure $next    Closure rantai middleware berikutnya.
     * @return Response Respon HTTP redirect, JSON unauthorized, atau respon lanjut ke controller.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Abaikan verifikasi jika pengguna sedang mengakses endpoint logout
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        // 2. Lakukan pengecekan hanya jika pengguna telah terotentikasi
        if (Auth::check() && $request->hasSession()) {
            $user = Auth::user();
            $currentSessionId = $request->session()->getId();

            // 3. Baca ID sesi aktif yang terdaftar di cache server untuk akun ini
            $cacheKey = 'active_user_session_' . $user->id_akun;
            $activeSessionId = Cache::get($cacheKey);

            // 4. Jika cache telah terisi ID sesi dan berbeda dengan sesi peramban saat ini,
            //    berarti akun baru saja masuk dari perangkat/peramban lain.
            if ($activeSessionId && $activeSessionId !== $currentSessionId) {
                // Hentikan sesi lama di peramban ini secara menyeluruh
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $pesanPeringatan = 'Sesi Anda telah berakhir karena akun ini baru saja login di perangkat atau peramban lain demi keamanan.';

                // Tangani respons jika permintaan berupa AJAX / Fetch API
                if ($request->expectsJson()) {
                    return response()->json([
                        'message'  => $pesanPeringatan,
                        'redirect' => route('login'),
                    ], 401);
                }

                // Alihkan ke halaman login dengan notifikasi peringatan
                return redirect()->route('login')->with('warning', $pesanPeringatan);
            }
        }

        return $next($request);
    }
}

