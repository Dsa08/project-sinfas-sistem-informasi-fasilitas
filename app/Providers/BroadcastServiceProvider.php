<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider BroadcastServiceProvider
 *
 * Menginisialisasi infrastruktur penyiaran data secara *real-time* (event broadcasting) pada SINFAS.
 * Mendaftarkan endpoint autentikasi saluran broadcast dan memuat berkas otorisasi `routes/channels.php`.
 */
class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Memulai rute broadcast dan memuat saluran komunikasi privat antarperamban.
     *
     * Efek Samping:
     * - Mendaftarkan rute internal `/broadcasting/auth` untuk otorisasi koneksi WebSocket / Pusher.
     * - Memuat definisi saluran dari `routes/channels.php`.
     *
     * @return void
     */
    public function boot(): void
    {
        Broadcast::routes();

        require base_path('routes/channels.php');
    }
}
