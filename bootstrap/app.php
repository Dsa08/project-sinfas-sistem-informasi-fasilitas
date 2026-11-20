<?php

/**
 * Bootstrap Aplikasi SINFAS
 *
 * Berkas inisialisasi awal framework Laravel yang bertugas:
 * 1. Membuat instansi kontainer IoC (`Illuminate\Foundation\Application`) sebagai inti aplikasi.
 * 2. Mengikat (*bind*) kontrak kernel HTTP, Console/Artisan, dan Exception Handler kustom SINFAS.
 * 3. Mengembalikan objek `$app` yang siap memproses request masuk dari web maupun CLI.
 */

/*
|--------------------------------------------------------------------------
| 1. Inisialisasi Instansi Aplikasi (IoC Container)
|--------------------------------------------------------------------------
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| 2. Pengikatan Antarmuka Penting (Kernel & Exception Handler)
|--------------------------------------------------------------------------
| Mengikat implementasi HTTP Kernel (middleware web/auth), Console Kernel
| (penjadwalan artisan), dan Exception Handler terpusat sebagai singleton.
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| 3. Kembalikan Instansi Aplikasi
|--------------------------------------------------------------------------
*/

return $app;
