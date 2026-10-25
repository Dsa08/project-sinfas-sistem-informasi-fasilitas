<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Tambahkan nilai 'dibatalkan' pada enum status_pengajuan tabel peminjaman.
 * 
 * Memungkinkan sistem mencatat status pembatalan mandiri oleh peminjam (soft status)
 * tanpa menghapus baris transaksi agar integritas riwayat dan jejak audit tetap terjaga.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE peminjaman MODIFY COLUMN status_pengajuan ENUM('menunggu', 'disetujui', 'ditolak', 'dibatalkan') NOT NULL DEFAULT 'menunggu'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE peminjaman MODIFY COLUMN status_pengajuan ENUM('menunggu', 'disetujui', 'ditolak') NOT NULL DEFAULT 'menunggu'");
    }
};
