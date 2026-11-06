<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Pembuatan Tabel Master Pegawai.
 *
 * Tujuan:
 * Menyimpan data induk guru dan tenaga kependidikan sekolah (NIP sebagai primary key string unik,
 * nama lengkap, dan relasi akun).
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel pegawai.
     *
     * Efek Samping:
     * - Membuat tabel baru `pegawai` dengan kolom primary key `nip`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->string('nip', 20)->primary();
            $table->string('nama');
            $table->timestamps();
        });
    }

    /**
     * Membatalkan migrasi dan menghapus tabel pegawai.
     *
     * Efek Samping:
     * - Menghapus tabel `pegawai` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
