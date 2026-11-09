<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Create Stored Procedure Rekapitulasi Peminjaman Bulanan
 *
 * Mengimplementasikan Stored Procedure pada tingkat mesin basis data MySQL
 * untuk menghitung rekapitulasi performa transaksi peminjaman sarana secara terpusat:
 * 1. sp_rekapitulasi_peminjaman_bulanan(IN p_bulan, IN p_tahun):
 *    Menerima parameter bulan dan tahun, lalu menghasilkan agregasi statistik:
 *    - total_pengajuan
 *    - total_disetujui
 *    - total_ditolak
 *    - total_dibatalkan
 *    - total_sedang_dipinjam
 *    - total_kembali_tepat_waktu
 *    - total_terlambat
 *
 * Tidak mengubah struktur skema tabel (0% perubahan ERD).
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan stored procedure pada basis data MySQL.
     *
     * Efek Samping:
     * - Menerbitkan procedure SQL `sp_rekapitulasi_peminjaman_bulanan` ke dalam basis data.
     *
     * @return void
     */
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_rekapitulasi_peminjaman_bulanan;
            CREATE PROCEDURE sp_rekapitulasi_peminjaman_bulanan(
                IN p_bulan INT,
                IN p_tahun INT
            )
            BEGIN
                SELECT 
                    p_bulan AS bulan,
                    p_tahun AS tahun,
                    COUNT(p.kode_pinjam) AS total_pengajuan,
                    COALESCE(SUM(CASE WHEN p.status_pengajuan = 'disetujui' THEN 1 ELSE 0 END), 0) AS total_disetujui,
                    COALESCE(SUM(CASE WHEN p.status_pengajuan = 'ditolak' THEN 1 ELSE 0 END), 0) AS total_ditolak,
                    COALESCE(SUM(CASE WHEN p.status_pengajuan = 'dibatalkan' THEN 1 ELSE 0 END), 0) AS total_dibatalkan,
                    COALESCE(SUM(CASE WHEN p.status_pengajuan = 'disetujui' AND (k.kondisi_barang IS NULL) THEN 1 ELSE 0 END), 0) AS total_sedang_dipinjam,
                    COALESCE(SUM(CASE WHEN k.kondisi_barang IS NOT NULL AND DATEDIFF(k.tanggal_kembali, p.tanggal_pinjam) <= 3 THEN 1 ELSE 0 END), 0) AS total_kembali_tepat_waktu,
                    COALESCE(SUM(CASE WHEN (k.kondisi_barang IS NOT NULL AND DATEDIFF(k.tanggal_kembali, p.tanggal_pinjam) > 3) 
                              OR (p.status_pengajuan = 'disetujui' AND k.kondisi_barang IS NULL AND CURDATE() > DATE_ADD(p.tanggal_pinjam, INTERVAL 3 DAY)) 
                             THEN 1 ELSE 0 END), 0) AS total_terlambat
                FROM peminjaman p
                LEFT JOIN pengembalian k ON p.kode_pinjam = k.kode_pinjam
                WHERE (p_bulan = 0 OR MONTH(p.tanggal_pinjam) = p_bulan)
                  AND YEAR(p.tanggal_pinjam) = p_tahun;
            END;
        ");
    }

    /**
     * Membatalkan migrasi dan melepas stored procedure dari basis data.
     *
     * Efek Samping:
     * - Menghapus procedure SQL `sp_rekapitulasi_peminjaman_bulanan` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_rekapitulasi_peminjaman_bulanan;");
    }
};

