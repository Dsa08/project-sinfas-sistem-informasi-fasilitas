<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Create Stored Functions Peminjaman
 * 
 * Mengimplementasikan Stored Functions pada level RDBMS (MySQL/MariaDB) untuk efisiensi logika bisnis:
 * 1. fn_sisa_kuota_siswa(p_nis): Menghitung sisa kuota peminjaman aktif siswa secara deterministik di DB (maks 2 alat).
 * 2. fn_hitung_terlambat_hari(p_tgl_pinjam, p_tgl_kembali): Menghitung selisih hari keterlambatan pengembalian
 *    berdasarkan durasi standar peminjaman (3 hari).
 */
return new class extends Migration
{
    /**
     * Menjalankan migration dan mendefinisikan stored functions.
     */
    public function up(): void
    {
        // 1. Function: Hitung sisa kuota siswa (Maksimal 2 alat aktif)
        DB::unprepared("
            DROP FUNCTION IF EXISTS fn_sisa_kuota_siswa;
            CREATE FUNCTION fn_sisa_kuota_siswa(p_nis VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci)
            RETURNS INT
            DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE v_aktif INT DEFAULT 0;
                
                SELECT COUNT(*) INTO v_aktif
                FROM peminjaman p
                LEFT JOIN pengembalian k ON p.kode_pinjam = k.kode_pinjam
                WHERE p.nis = p_nis COLLATE utf8mb4_unicode_ci
                  AND p.status_pengajuan IN ('menunggu', 'disetujui')
                  AND (k.kondisi_barang IS NULL);
                  
                RETURN GREATEST(0, 2 - v_aktif);
            END;
        ");

        // 2. Function: Hitung keterlambatan pengembalian (Durasi batas pinjam standar 3 hari)
        DB::unprepared("
            DROP FUNCTION IF EXISTS fn_hitung_terlambat_hari;
            CREATE FUNCTION fn_hitung_terlambat_hari(p_tgl_pinjam DATE, p_tgl_kembali DATE)
            RETURNS INT
            DETERMINISTIC
            NO SQL
            BEGIN
                DECLARE v_durasi INT DEFAULT 0;
                SET v_durasi = DATEDIFF(p_tgl_kembali, p_tgl_pinjam);
                
                IF v_durasi > 3 THEN
                    RETURN v_durasi - 3;
                ELSE
                    RETURN 0;
                END IF;
            END;
        ");
    }

    /**
     * Membatalkan migration dan menghapus stored functions.
     */
    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS fn_sisa_kuota_siswa;");
        DB::unprepared("DROP FUNCTION IF EXISTS fn_hitung_terlambat_hari;");
    }
};
