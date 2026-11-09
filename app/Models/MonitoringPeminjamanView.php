<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model MonitoringPeminjamanView
 *
 * Model Eloquent read-only yang merepresentasikan Database View `v_monitoring_peminjaman_aktif`.
 * Menggabungkan data dari 6 tabel fisik (peminjaman, barang, kategori, akun, siswa, pegawai, pengembalian)
 * secara virtual tanpa membebani penyimpanan fisik basis data (0% perubahan ERD).
 *
 * @property string $kode_pinjam Kode transaksi peminjaman sarana.
 * @property string $tanggal_pinjam Tanggal mulai peminjaman.
 * @property string $batas_kembali Batas waktu maksimal peminjaman (durasi standar 3 hari).
 * @property string $status_pengajuan Status persetujuan ('menunggu', 'disetujui', 'ditolak', 'dibatalkan').
 * @property string $lokasi_penggunaan Tempat fisik penggunaan barang.
 * @property string $keterangan_penggunaan Keperluan operasional peminjaman.
 * @property string|null $alasan_penolakan Alasan penolakan dari admin sarana jika status ditolak.
 * @property string $kode_barang Kode inventaris sarana yang dipinjam.
 * @property string $nama_barang Nama alat sarana.
 * @property string|null $merk_model Merk produsen dan model alat.
 * @property string|null $nama_kategori Nama kategori sarana prasarana.
 * @property int $id_akun Kunci identitas akun peminjam.
 * @property string $role_peminjam Hak akses akun peminjam ('siswa' atau 'pegawai').
 * @property string $nama_peminjam Nama lengkap peminjam.
 * @property string $nomor_identitas NIS siswa atau NIP pegawai.
 * @property string $unit_kelas Kelas siswa atau jabatan pegawai.
 * @property string $nomor_kontak Nomor WhatsApp / handphone peminjam.
 * @property string|null $kode_kembali Kode transaksi pengembalian barang jika sudah dikembalikan.
 * @property string|null $tanggal_kembali Tanggal fisik serah terima pengembalian alat.
 * @property string|null $kondisi_kembali Kondisi fisik saat diterima ('Baik', 'Kurang Baik', 'Rusak Berat').
 * @property string|null $catatan_kembali Catatan inspeksi fisik saat pengembalian.
 * @property string $status_operasional Ringkasan operasional ('menunggu_verifikasi', 'sedang_dipinjam', 'terlambat', 'sudah_kembali', 'ditolak', 'dibatalkan').
 */
class MonitoringPeminjamanView extends Model
{
    /**
     * Nama view SQL basis data.
     *
     * @var string
     */
    protected $table = 'v_monitoring_peminjaman_aktif';

    /**
     * Primary key unik dari baris view.
     *
     * @var string
     */
    protected $primaryKey = 'kode_pinjam';

    /**
     * Tipe primary key bukan auto-incrementing integer.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tipe data primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * View ini bersifat read-only sehingga tidak mengelola timestamps mandiri.
     *
     * @var bool
     */
    public $timestamps = false;
}

