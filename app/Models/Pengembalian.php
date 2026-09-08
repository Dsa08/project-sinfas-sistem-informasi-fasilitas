<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Pengembalian
 * 
 * Merepresentasikan pencatatan pengembalian sarana yang telah selesai dipinjam siswa.
 * Memuat tanggal fisik barang dikembalikan, kondisi saat kembali (baik, kurang baik, rusak),
 * bukti upload (foto/video), serta catatan tambahan dari siswa atau admin sarana.
 */
class Pengembalian extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'pengembalian';

    /**
     * Kunci utama bertipe kode unik string (contoh: 'KMB-20260908-XYZ').
     *
     * @var string
     */
    protected $primaryKey = 'kode_kembali';

    /**
     * Kunci utama tidak bersifat auto-incrementing integer.
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
     * Kolom-kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_kembali',       // Kode unik transaksi pengembalian
        'kode_pinjam',        // Foreign key merujuk ke tabel peminjaman
        'tanggal_kembali',    // Tanggal aktual sarana dikembalikan
        'kondisi_barang',     // Kondisi saat dikembalikan: 'baik', 'kurang_baik', 'rusak_berat'
        'bukti_foto_video',   // Path file bukti fisik pengembalian
        'catatan',            // Catatan hasil inspeksi / serah terima barang
    ];

    /**
     * Konversi tipe data atribut Eloquent.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_kembali' => 'date',
    ];

    /**
     * Relasi: Setiap data pengembalian merujuk tepat ke satu permohonan peminjaman (N:1).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'kode_pinjam', 'kode_pinjam');
    }
}
