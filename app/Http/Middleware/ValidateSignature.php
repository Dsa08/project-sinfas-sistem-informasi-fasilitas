<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ValidateSignature as Middleware;

/**
 * Middleware ValidateSignature
 *
 * Memverifikasi keaslian dan masa berlaku tanda tangan digital kriptografis pada URL tertanda (*signed URLs*).
 * Digunakan untuk mencegah manipulasi parameter kueri saat pengguna mengakses tautan verifikasi atau reset sandi.
 */
class ValidateSignature extends Middleware
{
    /**
     * Daftar nama parameter query string pihak ketiga yang diabaikan saat validasi hash signature.
     *
     * @var array<int, string>
     */
    protected $except = [
        // 'fbclid',
        // 'utm_campaign',
        // 'utm_content',
        // 'utm_medium',
        // 'utm_source',
        // 'utm_term',
    ];
}
