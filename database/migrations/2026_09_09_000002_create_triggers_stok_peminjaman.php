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
        // 1. Trigger: Otomatis kurangi stok saat peminjaman disetujui
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_kurangi_stok_peminjaman;
            CREATE TRIGGER trg_kurangi_stok_peminjaman
            AFTER UPDATE ON peminjaman
            FOR EACH ROW
            BEGIN
                IF OLD.status_pengajuan = 'menunggu' AND NEW.status_pengajuan = 'disetujui' THEN
                    UPDATE barang 
                    SET jumlah_baik = GREATEST(0, jumlah_baik - 1)
                    WHERE kode_barang = NEW.kode_barang;
                END IF;
            END;
        ");

        // 2. Trigger: Otomatis tambah stok sesuai kondisi fisik saat pengembalian diverifikasi
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_kembalikan_stok_barang;
            CREATE TRIGGER trg_kembalikan_stok_barang
            AFTER UPDATE ON pengembalian
            FOR EACH ROW
            BEGIN
                DECLARE v_kode_barang VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
                
                IF OLD.kondisi_barang IS NULL AND NEW.kondisi_barang IS NOT NULL THEN
                    SELECT kode_barang INTO v_kode_barang
                    FROM peminjaman
                    WHERE kode_pinjam = NEW.kode_pinjam;
                    
                    IF NEW.kondisi_barang = 'Baik' THEN
                        UPDATE barang SET jumlah_baik = jumlah_baik + 1 WHERE kode_barang = v_kode_barang;
                    ELSEIF NEW.kondisi_barang = 'Kurang Baik' THEN
                        UPDATE barang SET jumlah_kurang_baik = jumlah_kurang_baik + 1 WHERE kode_barang = v_kode_barang;
                    ELSEIF NEW.kondisi_barang = 'Rusak Berat' THEN
                        UPDATE barang SET jumlah_rusak_berat = jumlah_rusak_berat + 1 WHERE kode_barang = v_kode_barang;
                    END IF;
                END IF;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_kurangi_stok_peminjaman;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_kembalikan_stok_barang;");
    }
};
