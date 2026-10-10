<?php

namespace App\Support;

use App\Models\Akun;

/** Menyatukan tujuan awal per role agar controller dan middleware konsisten. */
final class RoleHome
{
    /** Tentukan halaman pertama yang boleh dibuka berdasarkan role akun. */
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
