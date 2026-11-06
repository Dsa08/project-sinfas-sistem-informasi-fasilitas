<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

/**
 * Service Provider EventServiceProvider
 *
 * Mengelola pendaftaran relasi antara peristiwa sistem (*events*) dan penangan peristiwa (*listeners*).
 * Memungkinkan pemrosesan aksi asinkron atau terpisah ketika suatu kejadian bisnis terjadi di SINFAS.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * Pemetaan kelas event ke listener yang bertugas menanganinya.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Mendaftarkan konfigurasi event kustom aplikasi saat proses bootstrap berlangsung.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Menentukan apakah event dan listener harus ditemukan secara otomatis (event discovery).
     *
     * @return bool False untuk menjaga pendaftaran event tetap eksplisit dan terprediksi.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
