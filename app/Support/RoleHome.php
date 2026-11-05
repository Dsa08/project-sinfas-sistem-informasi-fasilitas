<?php

namespace App\Support;

use App\Models\Akun;

/**
 * Helper Class RoleHome
 *
 * Menyatukan dan menstandarisasi resolusi URL halaman beranda awal (home destination)
 * untuk setiap peran pengguna (Role-Based Access Control) di seluruh controller dan middleware aplikasi.
 */
final class RoleHome
{
    /**
     * Menentukan rute URL halaman beranda pertama yang berhak diakses berdasarkan role akun.
     *
     * Aturan Pemetaan:
     * - 'siswa' / 'pegawai' => Halaman katalog dan peminjaman siswa (`route('dashboard')`).
     * - 'admin_sarana'     => Panel kontrol analitik inventaris sarana (`route('admin.dashboard')`).
     * - 'admin_sistem'     => Panel kontrol manajemen akun dan sistem (`route('admin.sistem.dashboard')`).
     * - default            => Halaman landing publik (`route('home')`).
     *
     * @param Akun $akun Model akun pengguna yang sedang terautentikasi.
     * @return string URL absolut halaman tujuan awal yang valid untuk peran akun tersebut.
     */
    public static function url(Akun $akun): string
    {
        return match ($akun->role) {
            'siswa', 'pegawai' => route('dashboard'),
            'admin_sarana' => route('admin.dashboard'),
            'admin_sistem' => route('admin.sistem.dashboard'),
            default => route('home'),
        };
    }
}
