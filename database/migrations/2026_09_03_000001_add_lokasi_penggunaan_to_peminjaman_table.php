<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Penambahan Kolom Lokasi Penggunaan pada Tabel Peminjaman.
 *
 * Tujuan:
 * Menampung informasi spesifik mengenai ruangan, laboratorium, aula, atau area sekolah
 * tempat sarana prasarana yang dipinjam akan digunakan oleh siswa/pegawai.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi penambahan kolom lokasi_penggunaan.
     *
     * Efek Samping:
     * - Menambahkan kolom `lokasi_penggunaan` (VARCHAR 255, nullable) setelah kolom `keterangan_penggunaan`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->string('lokasi_penggunaan')->nullable()->after('keterangan_penggunaan');
        });
    }

    /**
     * Membatalkan migrasi dan menghapus kolom lokasi_penggunaan.
     *
     * Efek Samping:
     * - Menghapus kolom `lokasi_penggunaan` dari tabel `peminjaman`.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn('lokasi_penggunaan');
        });
    }
};
