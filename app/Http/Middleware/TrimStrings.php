<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

/**
 * Middleware TrimStrings
 *
 * Menghapus spasi putih tidak disengaja di awal dan akhir teks pada setiap input request HTTP,
 * dengan mengecualikan kolom kata sandi agar integritas karakter otentikasi tetap terjaga.
 */
class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];
}
