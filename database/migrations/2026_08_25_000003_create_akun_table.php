<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Pembuatan Tabel Akun Pengguna Awal.
 *
 * Tujuan:
 * Menyimpan data akun autentikasi utama (username unik, hash kata sandi, nama, kontak, dan peran pengguna).
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel akun.
     *
     * Efek Samping:
     * - Membuat tabel baru `akun` dengan primary key auto-increment `id_akun`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('akun', function (Blueprint $table) {
            $table->id('id_akun');
            $table->string('nis', 20)->nullable();
            $table->string('nip', 20)->nullable();
            $table->string('nama');
            $table->string('nomor_kontak', 20)->nullable();
            $table->enum('role', ['siswa', 'admin_sarana', 'admin_sistem']);
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamps();

            $table->foreign('nis')->references('nis')->on('siswa')->nullOnDelete();
            $table->foreign('nip')->references('nip')->on('pegawai')->nullOnDelete();
        });
    }

    /**
     * Membatalkan migrasi dan menghapus tabel akun.
     *
     * Efek Samping:
     * - Menghapus tabel `akun` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('akun');
    }
};
