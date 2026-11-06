<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Pembuatan Tabel Master Siswa.
 *
 * Tujuan:
 * Menyimpan data induk identitas siswa sekolah (NIS sebagai primary key string unik,
 * nama lengkap, email, nomor HP, dan relasi akun).
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel siswa.
     *
     * Efek Samping:
     * - Membuat tabel baru `siswa` dengan kolom primary key `nis`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->string('nis', 20)->primary();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Membatalkan migrasi dan menghapus tabel siswa.
     *
     * Efek Samping:
     * - Menghapus tabel `siswa` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
