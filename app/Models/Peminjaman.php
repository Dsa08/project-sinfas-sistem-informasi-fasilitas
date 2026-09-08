<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Peminjaman
 * 
 * Mengelola siklus transaksi permohonan peminjaman sarana oleh siswa.
 * Memuat informasi nomor pengajuan unik (kode_pinjam), identitas peminjam (nis),
 * barang yang dipinjam (kode_barang), rincian lokasi dan peruntukan, status verifikasi,
 * hingga alasan jika permohonan ditolak admin.
 */
class Peminjaman extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'peminjaman';

    /**
     * Kunci utama bertipe kode string (misal: 'PJM-20260908-ABCD').
     *
     * @var string
     */
    protected $primaryKey = 'kode_pinjam';

    /**
     * Primary key tidak auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tipe primary key string.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_pinjam',            // Kode transaksi unik peminjaman
        'nis',                    // Nomor Induk Siswa peminjam
        'kode_barang',            // Kode sarana prasarana yang diajukan
        'tanggal_pinjam',         // Tanggal rencana/mulai peminjaman
        'keterangan_penggunaan',  // Keperluan peminjaman (kegiatan belajar, lomba, dll.)
        'lokasi_penggunaan',      // Ruangan / area penggunaan barang
        'status_pengajuan',       // Status: 'menunggu', 'disetujui', 'ditolak', 'selesai'
        'alasan_penolakan',       // Catatan alasan dari admin bila status 'ditolak'
    ];

    /**
     * Konversi tipe data atribut Eloquent.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_pinjam' => 'date',
    ];

    /**
     * Relasi: Transaksi peminjaman diajukan oleh satu siswa (N:1).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }

    /**
     * Relasi: Transaksi peminjaman terkait dengan satu barang fisik (N:1).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    /**
     * Relasi: Satu transaksi peminjaman memiliki maksimal satu catatan pengembalian (1:1).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pengembalian()
    {
        return $this->hasOne(Pengembalian::class, 'kode_pinjam', 'kode_pinjam');
    }

    /**
     * Query Scope: Menyaring transaksi yang masih menunggu proses verifikasi admin.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMenunggu($query)
    {
        return $query->where('status_pengajuan', 'menunggu');
    }

    /**
     * Query Scope: Menyaring transaksi yang telah disetujui (barang sedang dipinjam).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisetujui($query)
    {
        return $query->where('status_pengajuan', 'disetujui');
    }
}
