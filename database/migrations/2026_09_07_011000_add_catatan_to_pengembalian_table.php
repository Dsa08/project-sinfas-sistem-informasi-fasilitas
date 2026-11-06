<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Menambahkan kolom catatan pada tabel pengembalian.
 *
 * Tujuan:
 * Menampung catatan inspeksi kondisi fisik barang, kelengkapan aksesoris,
 * atau keterangan serah terima dari staf sarana prasarana saat barang dikembalikan.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi skema tabel pengembalian.
     *
     * Efek Samping:
     * - Menambahkan kolom `catatan` bertipe text (nullable) setelah kolom `kondisi_barang`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('pengembalian', function (Blueprint $table) {
            $table->text('catatan')->nullable()->after('kondisi_barang');
        });
    }

    /**
     * Membatalkan migrasi dan mengembalikan skema ke keadaan sebelumnya.
     *
     * Efek Samping:
     * - Menghapus kolom `catatan` dari tabel `pengembalian`.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('pengembalian', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });
    }
};
