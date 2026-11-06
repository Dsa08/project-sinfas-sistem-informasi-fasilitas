<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Pembuatan Tabel Pengembalian Sarana.
 *
 * Tujuan:
 * Mencatat transaksi pengembalian barang yang telah selesai dipinjam (relasi 1:1 terhadap peminjaman),
 * tanggal kembali aktual, kondisi saat diterima, dan unggahan bukti fisik.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel pengembalian.
     *
     * Efek Samping:
     * - Membuat tabel baru `pengembalian` dengan primary key `kode_kembali` dan foreign key unique `kode_pinjam`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('pengembalian', function (Blueprint $table) {
            $table->string('kode_kembali', 30)->primary();
            // unique() -> menegakkan relasi 1 : 0..1 (satu peminjaman maksimal satu pengembalian,
            // dan baris ini baru dibuat setelah barang benar-benar dikembalikan, bukan mandatory sejak awal)
            $table->string('kode_pinjam', 30)->unique();
            $table->date('tanggal_kembali');
            $table->string('kondisi_barang')->nullable();
            $table->string('bukti_foto_video')->nullable();
            $table->timestamps();

            $table->foreign('kode_pinjam')->references('kode_pinjam')->on('peminjaman');
        });
    }

    /**
     * Membatalkan migrasi dan menghapus tabel pengembalian.
     *
     * Efek Samping:
     * - Menghapus tabel `pengembalian` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('pengembalian');
    }
};
