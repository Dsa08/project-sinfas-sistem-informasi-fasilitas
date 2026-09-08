<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware RoleMiddleware
 * 
 * Mengimplementasikan Role-Based Access Control (RBAC) pada aplikasi SINFAS.
 * Memastikan pengguna yang mengakses rute tertentu memiliki salah satu dari
 * hak akses (role) yang diizinkan (misal: 'siswa', 'admin_sarana', 'admin_sistem').
 * 
 * Penggunaan pada Route:
 *   Route::middleware('role:admin_sarana')->group(...)
 *   Route::middleware('role:admin_sarana,admin_sistem')->group(...)
 */
class RoleMiddleware
{
    /**
     * Memfilter setiap request yang masuk berdasarkan role pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles Daftar role yang diizinkan mengakses endpoint
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Validasi Autentikasi: Jika belum login, redirect ke formulir login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 2. Validasi Hak Akses (Role): Periksa apakah role user terdaftar dalam parameter $roles
        if (!in_array($user->role, $roles)) {
            // Tolak dengan HTTP 403 Forbidden bila hak akses tidak sesuai
            abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // 3. Lanjutkan request jika otorisasi valid
        return $next($request);
    }
}
