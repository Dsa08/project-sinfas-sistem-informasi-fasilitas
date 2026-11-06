<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

/**
 * Middleware PreventRequestsDuringMaintenance
 *
 * Mengalihkan seluruh permintaan pengguna ke halaman pemeliharaan (HTTP status 503)
 * saat aplikasi SINFAS diaktifkan dalam mode perawatan sistem via perintah `php artisan down`.
 */
class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * Daftar URI yang tetap dapat diakses meskipun sistem sedang dalam mode pemeliharaan.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
