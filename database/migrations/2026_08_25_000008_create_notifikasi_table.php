<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Pembuatan Tabel Notifikasi Awal.
 *
 * Tujuan:
 * Menyimpan pesan pemberitahuan in-app ke pengguna sistem berdasarkan foreign key `id_akun`.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel notifikasi.
     *
     * Efek Samping:
     * - Membuat tabel baru `notifikasi` dengan foreign key ke tabel `akun`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->foreignId('id_akun')->constrained('akun', 'id_akun');
            $table->text('pesan');
            $table->boolean('status_baca')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Membatalkan migrasi dan menghapus tabel notifikasi.
     *
     * Efek Samping:
     * - Menghapus tabel `notifikasi` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
