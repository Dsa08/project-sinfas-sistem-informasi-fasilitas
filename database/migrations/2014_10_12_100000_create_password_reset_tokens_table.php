<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Pembuatan Tabel Penyimpanan Token Reset Kata Sandi.
 *
 * Tujuan:
 * Menampung token acak kriptografis yang dikirimkan ke surel pengguna saat melakukan pemulihan akses akun.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel password_reset_tokens.
     *
     * Efek Samping:
     * - Membuat tabel baru `password_reset_tokens` dengan kolom primary key `email`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Membatalkan migrasi dan menghapus tabel password_reset_tokens.
     *
     * Efek Samping:
     * - Menghapus tabel `password_reset_tokens` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
