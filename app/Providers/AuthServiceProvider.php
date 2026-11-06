<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Service Provider AuthServiceProvider
 *
 * Mengelola kebijakan autentikasi (*authentication*) dan otorisasi (*authorization*) aplikasi.
 * Bertanggung jawab mendaftarkan pemetaan Model ke Policy serta definisi Gate akses pada sistem SINFAS.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Pemetaan model entitas ke kelas kebijakan hak akses (policies).
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Mendaftarkan layanan autentikasi dan gerbang otorisasi aplikasi saat bootstrapping.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
