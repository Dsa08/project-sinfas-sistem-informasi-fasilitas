<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Pembaruan Skema Tabel Notifikasi untuk Sistem Notifikasi Terpusat.
 *
 * Tujuan:
 * Menambahkan kolom `tipe`, `judul`, `kode_pinjam` (relasi foreign key ke peminjaman),
 * dan `data_tambahan` (JSON) untuk mendukung notification bell in-app dan Web Push PWA.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi penambahan kolom-kolom struktur notifikasi.
     *
     * Efek Samping:
     * - Menambahkan kolom `tipe`, `judul`, `kode_pinjam`, dan `data_tambahan` ke tabel `notifikasi`.
     * - Menambahkan foreign key `kode_pinjam` dan indeks komposit `[id_akun, status_baca]`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->string('tipe', 50)->default('info_sistem')->after('id_akun');
            $table->string('judul', 255)->after('tipe');
            $table->string('kode_pinjam', 30)->nullable()->after('pesan');
            $table->json('data_tambahan')->nullable()->after('kode_pinjam');

            $table->foreign('kode_pinjam')
                  ->references('kode_pinjam')
                  ->on('peminjaman')
                  ->nullOnDelete();
                  
            $table->index(['id_akun', 'status_baca']);
        });
    }

    /**
     * Membatalkan migrasi dan melepas kolom serta foreign key terkait.
     *
     * Efek Samping:
     * - Melepas foreign key dan menghapus kolom yang ditambahkan.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->dropForeign(['kode_pinjam']);
            $table->dropIndex(['id_akun', 'status_baca']);
            $table->dropColumn(['tipe', 'judul', 'kode_pinjam', 'data_tambahan']);
        });
    }
};
