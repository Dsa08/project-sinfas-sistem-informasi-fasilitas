<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Menambahkan kolom penanda ganti kata sandi wajib pada tabel akun.
 *
 * Tujuan:
 * Menandai akun yang baru dibuat atau di-reset oleh Admin Sistem dengan flag `must_change_password = true`,
 * sehingga pengguna dipaksa mengganti kata sandi sementara saat pertama kali login.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi skema tabel akun.
     *
     * Efek Samping:
     * - Menambahkan kolom boolean `must_change_password` (default: false) setelah kolom `is_active`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            // Pengguna lama tetap menggunakan alur biasa; reset admin atau akun baru menyetel flag ini
            $table->boolean('must_change_password')->default(false)->after('is_active');
        });
    }

    /**
     * Membatalkan migrasi dan menghapus kolom penanda ganti sandi.
     *
     * Efek Samping:
     * - Menghapus kolom `must_change_password` dari tabel `akun`.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};
