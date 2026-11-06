<?php

namespace App\Http\Middleware;

use App\Models\Akun;
use App\Providers\RouteServiceProvider;
use App\Support\RoleHome;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware RedirectIfAuthenticated
 *
 * Mencegah pengguna yang telah berhasil masuk (terautentikasi) mengakses kembali rute publik tamu
 * seperti formulir login atau reset sandi. Secara cerdas mengarahkan pengguna langsung ke dasbor
 * awal masing-masing peran menggunakan helper `RoleHome::url()`.
 */
class RedirectIfAuthenticated
{
    /**
     * Memeriksa status login pengguna dan mengalihkan ke dasbor perannya jika sudah terautentikasi.
     *
     * Prasyarat / Kondisi:
     * - Pengguna mencoba membuka rute tamu (misal: '/login' atau '/forgot-password') saat sesinya masih aktif.
     *
     * Efek Samping:
     * - Mengalihkan langsung ke dasbor Siswa ('/dashboard'), Admin Sarana ('/admin/dashboard'),
     *   atau Admin Sistem ('/admin-sistem/dashboard') tanpa memproses pengontrol tamu.
     *
     * @param Request $request Request HTTP yang sedang diproses.
     * @param Closure $next Closure penanganan rantai middleware berikutnya.
     * @param string ...$guards Daftar guard autentikasi yang diperiksa (opsional).
     * @return Response Respon redirect ke halaman dasbor peran atau kelanjutan request tamu.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Alihkan ke halaman beranda spesifik peran jika model berupa akun SINFAS
                if ($user instanceof Akun) {
                    return redirect()->to(RoleHome::url($user));
                }

                // Fallback rute home default framework
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
