<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Create Database View Monitoring Peminjaman
 *
 * Mengimplementasikan Database View (Tabel Virtual) pada level RDBMS MySQL
 * untuk mengabstraksi kueri komprehensif relasi peminjaman sarana:
 * 1. v_monitoring_peminjaman_aktif:
 *    Menggabungkan relasi tabel `peminjaman`, `barang`, `kategori`, `akun`, `siswa`,
 *    `pegawai`, dan `pengembalian` menjadi satu tampilan entitas virtual siap pakai.
 *
 * Mengeliminasi penulisan klausa JOIN berulang pada lapisan Controller/Laporan.
 * Tidak mengubah struktur skema tabel (0% perubahan ERD).
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan database view pada basis data MySQL.
     *
     * Efek Samping:
     * - Menerbitkan view SQL `v_monitoring_peminjaman_aktif` ke dalam basis data.
     *
     * @return void
     */
    public function up(): void
    {
        DB::unprepared("
            DROP VIEW IF EXISTS v_monitoring_peminjaman_aktif;
            CREATE VIEW v_monitoring_peminjaman_aktif AS
            SELECT 
                p.kode_pinjam,
                p.tanggal_pinjam,
                DATE_ADD(p.tanggal_pinjam, INTERVAL 3 DAY) AS batas_kembali,
                p.status_pengajuan,
                p.lokasi_penggunaan,
                p.keterangan_penggunaan,
                p.alasan_penolakan,
                b.kode_barang,
                b.nama_barang,
                b.merk_model,
                kt.nama_kategori,
                a.id_akun,
                a.role AS role_peminjam,
                COALESCE(s.nama, pg.nama, a.nama, 'Pengguna') AS nama_peminjam,
                COALESCE(s.nis, pg.nip, a.username, '-') AS nomor_identitas,
                COALESCE(s.kelas, pg.jabatan, '-') AS unit_kelas,
                COALESCE(s.no_hp, pg.no_hp, a.nomor_kontak, '-') AS nomor_kontak,
                k.kode_kembali,
                k.tanggal_kembali,
                k.kondisi_barang AS kondisi_kembali,
                k.catatan AS catatan_kembali,
                CASE 
                    WHEN k.kondisi_barang IS NOT NULL THEN 'sudah_kembali'
                    WHEN p.status_pengajuan = 'disetujui' AND CURDATE() > DATE_ADD(p.tanggal_pinjam, INTERVAL 3 DAY) THEN 'terlambat'
                    WHEN p.status_pengajuan = 'disetujui' THEN 'sedang_dipinjam'
                    WHEN p.status_pengajuan = 'menunggu' THEN 'menunggu_verifikasi'
                    WHEN p.status_pengajuan = 'ditolak' THEN 'ditolak'
                    WHEN p.status_pengajuan = 'dibatalkan' THEN 'dibatalkan'
                    ELSE p.status_pengajuan
                END AS status_operasional
            FROM peminjaman p
            JOIN barang b ON p.kode_barang = b.kode_barang
            LEFT JOIN kategori kt ON b.id_kategori = kt.id_kategori
            LEFT JOIN akun a ON p.id_akun = a.id_akun
            LEFT JOIN siswa s ON (a.id_akun = s.id_akun OR (p.nis IS NOT NULL AND p.nis = s.nis))
            LEFT JOIN pegawai pg ON (a.id_akun = pg.id_akun)
            LEFT JOIN pengembalian k ON p.kode_pinjam = k.kode_pinjam;
        ");
    }

    /**
     * Membatalkan migrasi dan melepas database view dari basis data.
     *
     * Efek Samping:
     * - Menghapus view SQL `v_monitoring_peminjaman_aktif` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        DB::unprepared("DROP VIEW IF EXISTS v_monitoring_peminjaman_aktif;");
    }
};

