<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Pembuatan Tabel Transaksi Peminjaman Sarana.
 *
 * Tujuan:
 * Mengelola permohonan peminjaman sarana prasarana sekolah oleh siswa
 * (kode pinjam unik, foreign key nis dan kode_barang, tanggal mulai, keperluan, dan status pengajuan).
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel peminjaman.
     *
     * Efek Samping:
     * - Membuat tabel baru `peminjaman` dengan primary key `kode_pinjam` dan relasi ke tabel `siswa` dan `barang`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->string('kode_pinjam', 30)->primary();
            $table->string('nis', 20);
            $table->string('kode_barang', 30);
            $table->date('tanggal_pinjam');
            $table->text('keterangan_penggunaan')->nullable();
            $table->enum('status_pengajuan', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->timestamps();

            $table->foreign('nis')->references('nis')->on('siswa');
            $table->foreign('kode_barang')->references('kode_barang')->on('barang');
        });
    }

    /**
     * Membatalkan migrasi dan menghapus tabel peminjaman.
     *
     * Efek Samping:
     * - Menghapus tabel `peminjaman` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
