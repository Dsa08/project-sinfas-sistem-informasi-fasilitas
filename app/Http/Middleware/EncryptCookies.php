<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

/**
 * Middleware EncryptCookies
 *
 * Mengenkripsi seluruh nilai cookie HTTP yang dikirimkan ke peramban pengguna
 * untuk mencegah pembacaan data sensitif atau manipulasi cookie sesi oleh pihak ketiga.
 */
class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
