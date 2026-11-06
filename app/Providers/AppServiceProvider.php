<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider AppServiceProvider
 *
 * Penyedia layanan aplikasi utama (root service provider) pada SINFAS.
 * Tempat sentral untuk mengikat antarmuka ke implementasi pada IoC Container Laravel
 * dan mengonfigurasi perilaku global aplikasi saat bootstrapping.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Mendaftarkan pengikatan dependensi layanan (*service bindings*) ke dalam IoC Container.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Memulai layanan aplikasi global saat seluruh dependensi sistem telah terdaftar.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
