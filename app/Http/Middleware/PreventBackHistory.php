<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware PreventBackHistory
 *
 * Mencegah peramban (browser) menampilkan kembali halaman bertransaksi atau data sesi rahasia
 * dari memori cache peramban ketika pengguna menekan tombol "Back / Kembali" sesudah keluar (logout).
 * Menginjeksikan header HTTP standar anti-caching ke seluruh respon yang melewati rute terproteksi.
 */
class PreventBackHistory
{
    /**
     * Memproses respon HTTP dan menambahkan header larangan cache peramban.
     *
     * Efek Samping:
     * - Memodifikasi header respon HTTP dengan 'no-store, no-cache, must-revalidate' dan waktu kedaluwarsa lampau.
     *
     * @param Request $request Request HTTP yang diterima.
     * @param Closure $next Pipeline penanganan request HTTP berikutnya.
     * @return Response Respon HTTP yang telah dilengkapi header anti-caching peramban.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        return $response;
    }
}
