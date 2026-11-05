<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Penyetujuan
 *
 * Merepresentasikan pencatatan jejak audit (audit log) atas keputusan verifikasi transaksi peminjaman.
 * Mencatat identitas staf sarana prasarana yang menyetujui atau menolak permohonan pinjam,
 * status keputusan, serta catatan alasan penolakan.
 *
 * @property int $id_penyetujuan Kunci primer unik persetujuan.
 * @property int $id_peminjaman ID transaksi peminjaman yang diputuskan.
 * @property string $nip Nomor Induk Pegawai staf sarana yang mengeksekusi keputusan.
 * @property string $status Keputusan akhir verifikasi ('disetujui' atau 'ditolak').
 * @property string|null $catatan Alasan penolakan atau catatan tambahan dari staf.
 * @property-read Peminjaman $peminjaman Relasi data transaksi peminjaman terkait.
 * @property-read StaffSarana $staffSarana Relasi data staf sarana prasarana yang memverifikasi.
 */
class Penyetujuan extends Model
{
    /**
     * Nama tabel basis data yang diasosiasikan dengan model.
     *
     * @var string
     */
    protected $table = 'penyetujuan';

    /**
     * Nama kolom primary key pada tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_penyetujuan';

    /**
     * Menonaktifkan pengelolaan timestamp bawaan Laravel (created_at & updated_at)
     * karena tabel penyetujuan tidak memiliki kolom waktu default.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atribut kolom yang dapat diisi secara massal (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = ['id_peminjaman', 'nip', 'status', 'catatan'];

    /**
     * Mendapatkan relasi data transaksi peminjaman yang diputuskan.
     *
     * @return BelongsTo Relasi belongs-to ke model Peminjaman.
     */
    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman', 'id_peminjaman');
    }

    /**
     * Mendapatkan relasi data profil staf sarana yang mengeksekusi persetujuan.
     *
     * @return BelongsTo Relasi belongs-to ke model StaffSarana.
     */
    public function staffSarana(): BelongsTo
    {
        return $this->belongsTo(StaffSarana::class, 'nip', 'nip');
    }
}
