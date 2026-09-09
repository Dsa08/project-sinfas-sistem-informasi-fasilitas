<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
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
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS fn_sisa_kuota_siswa;");
        DB::unprepared("DROP FUNCTION IF EXISTS fn_hitung_terlambat_hari;");
    }
};
