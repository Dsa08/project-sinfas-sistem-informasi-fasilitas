<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
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
     * Reverse the migrations.
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
