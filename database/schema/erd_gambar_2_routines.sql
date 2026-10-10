-- Behavior-preserving equivalents of routines/triggers in the legacy sinfas DB.
-- Adapted for sinfas_baru: peminjaman is keyed by id_peminjaman and linked to
-- siswa through id_akun; pengembalian.kode_pinjam stores id_peminjaman.

DELIMITER //

DROP TRIGGER IF EXISTS trg_kurangi_stok_peminjaman//
CREATE TRIGGER trg_kurangi_stok_peminjaman
AFTER UPDATE ON peminjaman
FOR EACH ROW
BEGIN
    IF OLD.status_pengajuan = 'menunggu' AND NEW.status_pengajuan = 'disetujui' THEN
        UPDATE barang
        SET jumlah_baik = GREATEST(0, jumlah_baik - 1)
        WHERE kode_barang = NEW.kode_barang;
    END IF;
END//

DROP TRIGGER IF EXISTS trg_kembalikan_stok_barang//
CREATE TRIGGER trg_kembalikan_stok_barang
AFTER UPDATE ON pengembalian
FOR EACH ROW
BEGIN
    DECLARE v_kode_barang VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

    IF OLD.kondisi_barang IS NULL AND NEW.kondisi_barang IS NOT NULL THEN
        SELECT kode_barang INTO v_kode_barang
        FROM peminjaman
        WHERE id_peminjaman = NEW.kode_pinjam;

        IF NEW.kondisi_barang = 'Baik' THEN
            UPDATE barang SET jumlah_baik = jumlah_baik + 1 WHERE kode_barang = v_kode_barang;
        ELSEIF NEW.kondisi_barang = 'Kurang Baik' THEN
            UPDATE barang SET jumlah_kurang_baik = jumlah_kurang_baik + 1 WHERE kode_barang = v_kode_barang;
        ELSEIF NEW.kondisi_barang = 'Rusak Berat' THEN
            UPDATE barang SET jumlah_rusak_berat = jumlah_rusak_berat + 1 WHERE kode_barang = v_kode_barang;
        END IF;
    END IF;
END//

DROP FUNCTION IF EXISTS fn_sisa_kuota_siswa//
CREATE FUNCTION fn_sisa_kuota_siswa(p_nis VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_aktif INT DEFAULT 0;

    SELECT COUNT(*) INTO v_aktif
    FROM peminjaman p
    JOIN siswa s ON s.id_akun = p.id_akun
    LEFT JOIN pengembalian k ON p.id_peminjaman = k.kode_pinjam
    WHERE s.nis = p_nis COLLATE utf8mb4_unicode_ci
      AND p.status_pengajuan IN ('menunggu', 'disetujui')
      AND k.kondisi_barang IS NULL;

    RETURN GREATEST(0, 2 - v_aktif);
END//

DROP FUNCTION IF EXISTS fn_hitung_terlambat_hari//
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
END//
