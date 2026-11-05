<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

/**
 * Service SystemSettingsService
 *
 * Mengelola konfigurasi operasional global aplikasi (jam layanan peminjaman sarana,
 * status pemeliharaan, serta log aktivitas operasional admin sistem).
 * Menggunakan penyimpanan berbasis berkas JSON (`storage/app/system_settings.json`)
 * agar konfigurasi tetap persisten tanpa memerlukan tabel migrasi tambahan.
 */
class SystemSettingsService
{
    /**
     * Path absolut lokasi berkas penyimpanan JSON konfigurasi sistem.
     */
    protected static string $filePath = '';

    /**
     * Mendapatkan path absolut ke file konfigurasi sistem di storage lokal.
     *
     * @return string Path absolut ke berkas `system_settings.json`.
     */
    protected static function getFilePath(): string
    {
        if (empty(self::$filePath)) {
            self::$filePath = storage_path('app/system_settings.json');
        }
        return self::$filePath;
    }

    /**
     * Memuat seluruh konfigurasi pengaturan sistem dari berkas JSON lokal.
     * Menginisialisasi nilai default jika berkas belum ada.
     *
     * Efek Samping:
     * - Membuat berkas `system_settings.json` dengan nilai bawaan jika berkas belum ditemukan.
     *
     * @return array<string, mixed> Seluruh pasangan key-value konfigurasi sistem.
     */
    public static function all(): array
    {
        $path = self::getFilePath();

        if (!File::exists($path)) {
            // Nilai konfigurasi bawaan awal saat aplikasi pertama kali dipasang
            $default = [
                'start_time' => '07:00',
                'end_time' => '16:00',
                'last_backup_at' => null,
                'last_backup_file' => null,
                'custom_logs' => [],
            ];
            self::saveAll($default);
            return $default;
        }

        $content = File::get($path);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Mengambil satu nilai konfigurasi spesifik berdasarkan kunci pengenal.
     *
     * @param string $key Nama kunci pengaturan yang dicari (misal: 'start_time', 'end_time').
     * @param mixed $default Nilai fallback jika kunci pengaturan tidak ditemukan.
     * @return mixed Nilai pengaturan yang tersimpan atau nilai default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();
        return $all[$key] ?? $default;
    }

    /**
     * Menyimpan atau memperbarui satu nilai konfigurasi sistem berdasarkan kuncinya.
     *
     * Efek Samping:
     * - Memperbarui dan menulis ulang berkas `system_settings.json` pada disk.
     *
     * @param string $key Kunci pengaturan yang akan diperbarui nilainya.
     * @param mixed $value Nilai baru yang akan disimpan.
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        $all = self::all();
        $all[$key] = $value;
        self::saveAll($all);
    }

    /**
     * Menyimpan seluruh array konfigurasi ke dalam berkas fisik berformat JSON rapi.
     *
     * Efek Samping:
     * - Membuat direktori `storage/app/` jika belum tersedia.
     * - Menulis ulang konten berkas konfigurasi dengan format JSON berindentasi.
     *
     * @param array<string, mixed> $data Seluruh data konfigurasi yang akan dipersistenkan.
     * @return void
     */
    public static function saveAll(array $data): void
    {
        $dir = dirname(self::getFilePath());
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::put(self::getFilePath(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Mencatat riwayat tindakan administratif kustom ke dalam log internal sistem.
     *
     * Efek Samping:
     * - Menambahkan rekaman log ke daftar paling atas dan memangkas riwayat agar maksimal 30 entri terbaru.
     * - Menulis pembaruan log ke berkas konfigurasi JSON.
     *
     * @param string $user Nama lengkap atau username admin yang melakukan aksi.
     * @param string $action Deskripsi tindakan yang dilakukan (misal: 'Mengubah jam operasional').
     * @return void
     */
    public static function logActivity(string $user, string $action): void
    {
        $all = self::all();
        $logs = $all['custom_logs'] ?? [];

        array_unshift($logs, [
            'user' => $user,
            'action' => $action,
            'timestamp' => Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB',
            'created_at' => Carbon::now()->toIso8601String(),
        ]);

        // Simpan maksimal 30 log terbaru
        $all['custom_logs'] = array_slice($logs, 0, 30);
        self::saveAll($all);
    }
}
