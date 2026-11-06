<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Penambahan Kolom Email, Foto Avatar, dan Status Keaktifan pada Tabel Akun.
 *
 * Tujuan:
 * Mendukung fitur autentikasi surel mandiri, foto profil pengguna, serta fitur penonaktifan
 * akun sementara (soft deactivate) oleh Admin Sistem.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi penambahan kolom email, foto, dan is_active.
     *
     * Efek Samping:
     * - Menambahkan kolom `email` (VARCHAR 255 unique nullable), `foto` (VARCHAR 255 nullable),
     *   dan `is_active` (BOOLEAN default true) pada tabel `akun`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('nomor_kontak');
            $table->string('foto')->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('foto');
        });
    }

    /**
     * Membatalkan migrasi dan melepas kolom yang ditambahkan.
     *
     * Efek Samping:
     * - Menghapus kolom `email`, `foto`, dan `is_active` dari tabel `akun`.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            $table->dropColumn(['email', 'foto', 'is_active']);
        });
    }
};
