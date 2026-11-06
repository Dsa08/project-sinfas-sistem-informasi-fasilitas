<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

/**
 * Middleware VerifyCsrfToken
 *
 * Melindungi seluruh formulir POST, PUT, dan DELETE dari serangan Cross-Site Request Forgery (CSRF)
 * dengan memvalidasi token sesi rahasia sebelum permintaan dieksekusi oleh controller.
 */
class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
