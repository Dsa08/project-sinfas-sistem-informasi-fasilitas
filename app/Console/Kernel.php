<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel (Console)
 *
 * Mengelola penjadwalan tugas otomatis di latar belakang (cron jobs) dan pendaftaran perintah CLI Artisan kustom
 * pada aplikasi SINFAS. Bertanggung jawab mengeksekusi pemeriksaan berkala batas waktu pinjam sarana sekolah.
 */
class Kernel extends ConsoleKernel
{
    /**
     * Mendefinisikan jadwal eksekusi otomatis perintah konsol pada sistem.
     *
     * Jadwal Aktif:
     * - `sinfas:cek-batas-waktu`: Dijalankan setiap hari pukul 07:00 pagi untuk mendeteksi transaksi yang melebihi 3 hari.
     *
     * @param Schedule $schedule Instance penjadwal tugas Laravel.
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Pengecekan otomatis batas waktu peminjaman setiap hari pukul 07:00 pagi
        $schedule->command('sinfas:cek-batas-waktu')->dailyAt('07:00');
    }

    /**
     * Mendaftarkan perintah konsol berbasis berkas dan rute closure untuk aplikasi.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
