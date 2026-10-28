<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Service LoginAuditService
 *
 * Mengelola pencatatan riwayat audit login secara terpusat, aman, dan toleran terhadap kegagalan (fault-tolerant).
 * Kegagalan pada saat penulisan log tidak akan pernah menggagalkan proses login pengguna.
 */
class LoginAuditService
{
    /**
     * Catat satu aktivitas percobaan login ke tabel 'login_logs'.
     *
     * @param  Request      $request     Objek request HTTP dari klien
     * @param  string       $status      Hasil autentikasi ('sukses', 'gagal_password', dll.)
     * @param  Akun|null    $akun        Objek akun jika pengguna berhasil diidentifikasi
     * @param  string|null  $identifier  Identifier yang diketik pengguna (username/NIS/NIP)
     * @param  string|null  $keterangan  Penjelasan ringkas hasil autentikasi
     * @return LoginLog|null
     */
    public static function record(
        Request $request,
        string $status,
        ?Akun $akun = null,
        ?string $identifier = null,
        ?string $keterangan = null
    ): ?LoginLog {
        try {
            return LoginLog::create([
                'id_akun'     => $akun?->id_akun,
                'identifier'  => $identifier ?? $akun?->username ?? 'unknown',
                'ip_address'  => $request->ip(),
                'user_agent'  => substr($request->userAgent() ?? '', 0, 500),
                'status'      => $status,
                'keterangan'  => $keterangan,
                'login_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // Catat kegagalan sistem ke log Laravel agar tim developer dapat mendiagnosa tanpa mengganggu user
            Log::warning('LoginAuditService gagal mencatat log autentikasi: ' . $e->getMessage(), [
                'identifier' => $identifier,
                'status'     => $status,
            ]);

            return null;
        }
    }
}
