<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class SystemSettingsService
{
    protected static string $filePath = '';

    protected static function getFilePath(): string
    {
        if (empty(self::$filePath)) {
            self::$filePath = storage_path('app/system_settings.json');
        }
        return self::$filePath;
    }

    /**
     * Memuat semua pengaturan sistem dari file JSON.
     */
    public static function all(): array
    {
        $path = self::getFilePath();

        if (!File::exists($path)) {
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
     * Mengambil satu nilai pengaturan berdasarkan key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();
        return $all[$key] ?? $default;
    }

    /**
     * Menyimpan satu nilai pengaturan berdasarkan key.
     */
    public static function set(string $key, mixed $value): void
    {
        $all = self::all();
        $all[$key] = $value;
        self::saveAll($all);
    }

    /**
     * Menyimpan array pengaturan ke file.
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
     * Mencatat aksi kustom admin ke dalam activity log internal.
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
