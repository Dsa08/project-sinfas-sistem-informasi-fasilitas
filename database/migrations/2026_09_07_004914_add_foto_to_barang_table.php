<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Penambahan Kolom Foto Gambar pada Tabel Barang.
 *
 * Tujuan:
 * Menampung path relatif berkas gambar/foto visual sarana prasarana sekolah untuk
 * ditampilkan pada katalog peminjaman siswa dan inventaris admin sarana.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi penambahan kolom foto.
     *
     * Efek Samping:
     * - Menambahkan kolom `foto` (VARCHAR 255, nullable) setelah kolom `keterangan` pada tabel `barang`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->string('foto', 255)->nullable()->after('keterangan');
        });
    }

    /**
     * Membatalkan migrasi dan menghapus kolom foto.
     *
     * Efek Samping:
     * - Menghapus kolom `foto` dari tabel `barang`.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
