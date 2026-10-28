<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi: Menambahkan Kolom Two-Factor Authentication (2FA TOTP) pada Tabel akun.
 *
 * Kolom ini khusus digunakan untuk mengamankan akun Administrator (Admin Sarana & Admin Sistem):
 * - two_factor_secret: Secret key Base32 yang dienkripsi untuk Google Authenticator / Authenticator app.
 * - two_factor_recovery_codes: Daftar kode pemulihan darurat (recovery codes) yang dienkripsi.
 * - two_factor_confirmed_at: Timestamp konfirmasi aktivasi 2FA (jika null, berarti 2FA belum aktif).
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            $table->text('two_factor_secret')
                ->nullable()
                ->after('current_session_id')
                ->comment('Kunci rahasia 2FA TOTP (dienkripsi oleh Laravel)');

            $table->text('two_factor_recovery_codes')
                ->nullable()
                ->after('two_factor_secret')
                ->comment('Daftar kode darurat cadangan 2FA (dienkripsi oleh Laravel)');

            $table->timestamp('two_factor_confirmed_at')
                ->nullable()
                ->after('two_factor_recovery_codes')
                ->comment('Waktu aktivasi dan konfirmasi kode 2FA berhasil');
        });
    }

    /**
     * Kembalikan migrasi (rollback).
     */
    public function down(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
            ]);
        });
    }
};
