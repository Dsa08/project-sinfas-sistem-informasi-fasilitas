<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Throwable;

/**
 * Class Handler
 *
 * Pengelola penanganan pengecualian (exception handler) global aplikasi SINFAS.
 * Mengintersep error sistem, mengamankan data sensitif agar tidak masuk ke flash session,
 * serta menangani insiden kedaluwarsa token CSRF (419 TokenMismatchException) dengan pesan ramah pengguna.
 */
class Handler extends ExceptionHandler
{
    /**
     * Daftar nama input form yang tidak boleh disimpan ke flash session saat terjadi kegagalan validasi.
     * Mencegah pembocoran kata sandi pengguna ke riwayat sesi peramban.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Mendaftarkan callback penanganan pengecualian khusus untuk seluruh aplikasi.
     *
     * Penanganan Khusus:
     * - `TokenMismatchException`: Mengalihkan pengguna ke formulir login disertai pesan kesalahan sesi berakhir,
     *   atau mengembalikan JSON 419 untuk panggilan AJAX.
     *
     * @return void
     */
    public function register(): void
    {
        $this->renderable(function (TokenMismatchException $exception, Request $request) {
            $message = 'Sesi Anda telah berakhir. Silakan masuk kembali.';

            // Respons format JSON untuk permintaan berbasis AJAX / Fetch API
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 419);
            }

            // Respons redirect untuk peramban web reguler
            return redirect()->route('login')->with('error', $message);
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
