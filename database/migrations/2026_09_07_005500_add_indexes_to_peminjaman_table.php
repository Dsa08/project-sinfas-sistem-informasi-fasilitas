<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Penambahan Indeks Performa pada Tabel Peminjaman.
 *
 * Tujuan:
 * Mengoptimalkan kecepatan query pencarian riwayat peminjaman, filter status pengajuan,
 * dan pengecekan kuota aktif siswa melalui indeks pada kolom `nis`, `kode_barang`,
 * `status_pengajuan`, `tanggal_pinjam`, serta indeks komposit `[nis, status_pengajuan]`.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan indeks basis data.
     *
     * Efek Samping:
     * - Membuat indeks performa pada tabel `peminjaman`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->index('nis', 'idx_peminjaman_nis');
            $table->index('kode_barang', 'idx_peminjaman_kode_barang');
            $table->index('status_pengajuan', 'idx_peminjaman_status_pengajuan');
            $table->index('tanggal_pinjam', 'idx_peminjaman_tanggal_pinjam');
            $table->index(['nis', 'status_pengajuan'], 'idx_peminjaman_nis_status');
        });
    }

    /**
     * Membatalkan migrasi dan melepas seluruh indeks yang telah dibuat.
     *
     * Efek Samping:
     * - Menghapus indeks performa dari tabel `peminjaman`.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropIndex('idx_peminjaman_nis');
            $table->dropIndex('idx_peminjaman_kode_barang');
            $table->dropIndex('idx_peminjaman_status_pengajuan');
            $table->dropIndex('idx_peminjaman_tanggal_pinjam');
            $table->dropIndex('idx_peminjaman_nis_status');
        });
    }
};
