<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware DesktopOnlyAdmin
 *
 * Menegakkan kebijakan pembatasan akses antarmuka administratif (Admin Sarana & Admin Sistem)
 * agar hanya dapat dioperasikan melalui perangkat komputer/laptop (layar desktop).
 * Mencegah kesalahan input operasional data sarana dan verifikasi pada layar perangkat kecil (smartphone/tablet)
 * dengan mendeteksi User-Agent string serta Client Hints (`Sec-CH-UA-Mobile`).
 */
class DesktopOnlyAdmin
{
    /**
     * Memeriksa tipe perangkat pengguna sebelum memberikan akses ke panel administrasi.
     *
     * Prasyarat:
     * - Pengguna terautentikasi dan memiliki role `admin_sarana` atau `admin_sistem`.
     *
     * Efek Samping:
     * - Menghentikan alur request dan menyajikan halaman error HTTP 403 jika pengguna admin membuka via HP.
     * - Membiarkan rute `logout` tetap dapat diakses agar admin yang terlanjur login di HP bisa keluar.
     *
     * @param Request $request Request HTTP yang diterima dari klien peramban.
     * @param Closure $next Pipeline penanganan request HTTP berikutnya.
     * @return Response Respon kelanjutan request atau tampilan view peringatan 'errors.admin-desktop-only' (403).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        $userAgent = strtolower($request->userAgent() ?? '');
        $user = $request->user();
        $mobileClientHint = $request->header('Sec-CH-UA-Mobile') === '?1';
        $mobileUserAgent = preg_match(
            '/android|iphone|ipad|ipod|mobile|iemobile|opera mini|blackberry|webos|silk|kindle/',
            $userAgent
        ) === 1;

        if ($user && in_array($user->role, ['admin_sarana', 'admin_sistem'], true) && ($mobileClientHint || $mobileUserAgent)) {
            return response()->view('errors.admin-desktop-only', status: 403);
        }

        return $next($request);
    }
}
