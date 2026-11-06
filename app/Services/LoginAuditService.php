<?php

namespace App\Services;

use App\Models\Akun;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Service LoginAuditService
 *
 * Mengelola pencatatan dan peninjauan riwayat audit autentikasi pengguna secara mandiri.
 * Bekerja berbasis berkas log terstruktur (`storage/logs/auth-audit.log`) sehingga tidak
 * membutuhkan tabel baru pada basis data relasional (0% perubahan skema ERD).
 *
 * Tanggung Jawab:
 * 1. Mencatat percobaan login (sukses, sandi keliru, akun tidak ada, akun nonaktif, dan pemblokiran rate limit).
 * 2. Merekam metadata klien: alamat IP, User-Agent peramban, serta ringkasan sistem operasi/perangkat.
 * 3. Menyediakan fungsi pembacaan data audit terkini untuk dipantau oleh Administrator Sistem.
 * 4. Menerapkan pengamanan kesalahan (fault-tolerant) agar kegagalan pencatatan log tidak membatalkan proses login.
 */
class LoginAuditService
{
    /**
     * Path absolut tempat berkas log audit autentikasi disimpan di server lokal.
     *
     * @var string
     */
    private const LOG_FILE_PATH = 'logs/auth-audit.log';

    /**
     * Mencatat satu kejadian autentikasi ke dalam berkas log audit terstruktur.
     *
     * Prasyarat:
     * - Request HTTP valid tersedia untuk membaca IP dan User-Agent klien.
     * - Parameter `$status` harus salah satu dari: 'sukses', 'gagal_password', 'gagal_akun_tidak_ditemukan',
     *   'gagal_nonaktif', atau 'terkunci_rate_limit'.
     *
     * Efek Samping:
     * - Menulis satu baris berformat JSON-Lines ke berkas `storage/logs/auth-audit.log`.
     * - Menjamin transaksi login pengguna tetap berjalan lancar jika operasi berkas gagal.
     *
     * @param Request    $request    Objek HTTP request yang memuat metadata klien peramban.
     * @param string     $status     Kode status hasil autentikasi (contoh: 'sukses', 'gagal_password').
     * @param Akun|null  $akun       Model akun terautentikasi jika identitas ditemukan pada sistem.
     * @param string     $identifier String masukan awal pengguna (dapat berupa username, email, NIS, atau NIP).
     * @param string     $keterangan Ringkasan penjelasan teknis terkait alasan status tersebut.
     * @return void
     */
    public static function record(
        Request $request,
        string $status,
        ?Akun $akun = null,
        string $identifier = '',
        string $keterangan = ''
    ): void {
        try {
            // 1. Ekstraksi metadata perangkat dari User-Agent klien
            $userAgent = $request->userAgent() ?? 'Peramban Tidak Dikenal';
            $deviceSummary = self::parseDeviceSummary($userAgent);

            // 2. Susun baris log berformat array terstruktur
            $logEntry = [
                'id_log'           => (string) hrtime(true),
                'timestamp'        => Carbon::now()->toIso8601String(),
                'datetime'         => Carbon::now()->translatedFormat('d M Y, H:i:s') . ' WIB',
                'id_akun'          => $akun?->id_akun,
                'identifier'       => ! empty($identifier) ? $identifier : ($akun?->username ?? '-'),
                'nama'             => $akun?->nama ?? 'Pengguna Tidak Dikenal',
                'role'             => $akun?->role ?? '-',
                'role_label'       => $akun?->role_label ?? 'Tamu / Belum Terdaftar',
                'ip_address'       => $request->ip() ?? '127.0.0.1',
                'user_agent'       => $userAgent,
                'device_summary'   => $deviceSummary,
                'status'           => $status,
                'status_label'     => self::resolveStatusLabel($status),
                'badge_class'      => self::resolveBadgeClass($status),
                'keterangan'       => $keterangan,
            ];

            // 3. Tentukan direktori log dan pastikan folder storage tersedia
            $fullPath = storage_path(self::LOG_FILE_PATH);
            $directory = dirname($fullPath);
            if (! File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // 4. Tulis entri log baru ke baris paling akhir berkas (Append JSON Line)
            File::append($fullPath, json_encode($logEntry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        } catch (\Throwable $e) {
            // Pencatatan gagal di-fallback ke logger standar Laravel agar alur login utama tidak terganggu
            Log::warning('LoginAuditService gagal mencatat log audit: ' . $e->getMessage(), [
                'identifier' => $identifier,
                'status'     => $status,
            ]);
        }
    }

    /**
     * Membaca dan mem-parsing daftar riwayat audit login terbaru dari berkas log.
     * Mengembalikan data dengan urutan kronologis terbalik (kejadian paling baru berada di posisi teratas).
     *
     * @param int $limit Jumlah entri maksimal yang ingin dimuat (default: 15 entri).
     * @return array<int, array<string, mixed>> Koleksi entri log terstruktur yang siap dirender pada tabel blade.
     */
    public static function getRecentLogs(int $limit = 15): array
    {
        $fullPath = storage_path(self::LOG_FILE_PATH);

        // Jika berkas log belum terbentuk, kembalikan koleksi kosong
        if (! File::exists($fullPath)) {
            return [];
        }

        try {
            $content = trim(File::get($fullPath));
            if (empty($content)) {
                return [];
            }

            // Pecah isi berkas menjadi baris-baris terpisah
            $lines = explode(PHP_EOL, $content);
            $parsedLogs = [];

            // Baca dari baris paling akhir ke atas untuk mengutamakan data terkini
            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $line = trim($lines[$i]);
                if (empty($line)) {
                    continue;
                }

                $data = json_decode($line, true);
                if (is_array($data)) {
                    $parsedLogs[] = $data;
                }

                if (count($parsedLogs) >= $limit) {
                    break;
                }
            }

            return $parsedLogs;
        } catch (\Throwable $e) {
            Log::error('LoginAuditService gagal membaca log audit: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Menghasilkan ringkasan nama peramban dan sistem operasi yang ramah dibaca manusia.
     *
     * @param string $userAgent Header User-Agent mentah dari peramban klien.
     * @return string Label ringkasan perangkat (contoh: "Chrome di Windows", "Safari di iPhone").
     */
    public static function parseDeviceSummary(string $userAgent): string
    {
        $browser = 'Peramban Web';
        if (str_contains($userAgent, 'Edg/')) {
            $browser = 'Microsoft Edge';
        } elseif (str_contains($userAgent, 'Chrome/')) {
            $browser = 'Google Chrome';
        } elseif (str_contains($userAgent, 'Firefox/')) {
            $browser = 'Mozilla Firefox';
        } elseif (str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome/')) {
            $browser = 'Apple Safari';
        } elseif (str_contains($userAgent, 'Opera/') || str_contains($userAgent, 'OPR/')) {
            $browser = 'Opera';
        }

        $os = 'Desktop / Perangkat Lain';
        if (str_contains($userAgent, 'Windows NT 10.0')) {
            $os = 'Windows 10/11';
        } elseif (str_contains($userAgent, 'Windows NT')) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $os = 'iOS';
        } elseif (str_contains($userAgent, 'Mac OS X')) {
            $os = 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $os = 'Linux';
        }

        return "{$browser} ({$os})";
    }

    /**
     * Memetakan kode status internal ke label bahasa Indonesia yang ramah pengguna.
     *
     * @param string $status Kode status autentikasi.
     * @return string Label deskripsi status dalam bahasa Indonesia.
     */
    public static function resolveStatusLabel(string $status): string
    {
        return match ($status) {
            'sukses'                     => 'Login Berhasil',
            'gagal_password'             => 'Kata Sandi Keliru',
            'gagal_akun_tidak_ditemukan' => 'Akun Tidak Ditemukan',
            'gagal_nonaktif'             => 'Akun Dinonaktifkan',
            'terkunci_rate_limit'        => 'Diblokir Sementara (Rate Limit)',
            default                      => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /**
     * Menentukan kelas CSS penanda visual (badge) sesuai tingkat keparahan status autentikasi.
     *
     * @param string $status Kode status autentikasi.
     * @return string Nama kelas warna badge UI (contoh: "badge--success", "badge--danger").
     */
    public static function resolveBadgeClass(string $status): string
    {
        return match ($status) {
            'sukses'              => 'badge--success',
            'terkunci_rate_limit' => 'badge--warning',
            default               => 'badge--danger',
        };
    }
}

