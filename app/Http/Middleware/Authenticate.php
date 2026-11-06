<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

/**
 * Middleware Authenticate
 *
 * Mengamankan rute terproteksi dari akses pengguna tamu (guest/unauthenticated).
 * Jika pengguna belum masuk ke sistem, middleware ini secara otomatis mengarahkan
 * navigasi peramban ke formulir login atau mengembalikan respons status HTTP 401 jika permintaan berupa JSON/API.
 */
class Authenticate extends Middleware
{
    /**
     * Menentukan rute URL tujuan pengalihan (redirect destination) ketika pengguna belum terautentikasi.
     *
     * Prasyarat / Kondisi:
     * - Pengguna tidak memiliki sesi login yang valid di peramban saat mengakses rute terproteksi.
     *
     * Efek Samping:
     * - Mengalihkan request web biasa ke rute nama 'login' (`route('login')`).
     * - Mengembalikan null jika request meminta respons format JSON (Fetch/AJAX), sehingga Laravel melempar pengecualian 419/401.
     *
     * @param Request $request Request HTTP masuk yang belum memiliki sesi autentikasi.
     * @return string|null URL halaman login untuk request web, atau null jika request mengharapkan respons JSON.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
