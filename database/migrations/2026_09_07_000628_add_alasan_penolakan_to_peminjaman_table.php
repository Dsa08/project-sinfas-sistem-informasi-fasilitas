<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Penambahan Kolom Alasan Penolakan pada Tabel Peminjaman.
 *
 * Tujuan:
 * Menampung penjelasan resmi dari staf sarana ketika permohonan pinjam siswa ditolak
 * (misalnya karena bentrok jadwal ruangan, stok diprioritaskan untuk ujian, dll).
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi penambahan kolom alasan_penolakan.
     *
     * Efek Samping:
     * - Menambahkan kolom `alasan_penolakan` (TEXT, nullable) setelah kolom `status_pengajuan` pada tabel `peminjaman`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->text('alasan_penolakan')->nullable()->after('status_pengajuan');
        });
    }

    /**
     * Membatalkan migrasi dan menghapus kolom alasan_penolakan.
     *
     * Efek Samping:
     * - Menghapus kolom `alasan_penolakan` dari tabel `peminjaman`.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn('alasan_penolakan');
        });
    }
};
