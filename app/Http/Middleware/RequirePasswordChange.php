<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware RequirePasswordChange
 *
 * Menegakkan kewajiban penggantian kata sandi pertama kali bagi pengguna baru
 * atau pengguna yang sandinya baru saja di-reset oleh Admin Sistem (flag `must_change_password = true`).
 * Mengisolasi navigasi akun tersebut sehingga hanya diizinkan membuka formulir ganti sandi wajib
 * atau melakukan logout.
 */
class RequirePasswordChange
{
    /**
     * Memeriksa status kewajiban ganti sandi pada akun yang sedang login.
     *
     * Prasyarat:
     * - Akun pengguna telah terautentikasi dan memiliki atribut `must_change_password`.
     *
     * Efek Samping:
     * - Mengalihkan (redirect) paksa ke halaman pergantian sandi `password.change.show` jika akun belum memperbarui sandinya.
     *
     * @param Request $request Request HTTP yang masuk.
     * @param Closure $next Pipeline penanganan request HTTP berikutnya.
     * @return Response Respon kelanjutan request atau pengalihan rute ke formulir pergantian sandi wajib.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->user();
        $allowedRoutes = ['password.change.show', 'password.change.update', 'logout'];

        if ($account?->must_change_password && ! in_array($request->route()?->getName(), $allowedRoutes, true)) {
            return redirect()->route('password.change.show');
        }

        return $next($request);
    }
}
