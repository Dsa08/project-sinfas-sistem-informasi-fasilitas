<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Pengembalian
 * 
 * Merepresentasikan pencatatan pengembalian sarana yang telah selesai dipinjam pengguna.
 * Memuat tanggal fisik barang dikembalikan, kondisi saat kembali (baik, kurang baik, rusak berat),
 * berkas bukti fisik (foto/video), serta catatan hasil inspeksi serah terima oleh staf sarana prasarana.
 *
 * @property string $kode_kembali Kode unik transaksi pengembalian (kunci utama, misal: 'KMB-20260908-XYZ').
 * @property string $kode_pinjam Foreign key merujuk ke tabel peminjaman.
 * @property \Carbon\Carbon $tanggal_kembali Tanggal aktual sarana dikembalikan ke sekolah.
 * @property string|null $kondisi_barang Evaluasi kondisi fisik saat diterima ('baik', 'kurang_baik', 'rusak_berat').
 * @property string|null $bukti_foto_video Path relatif berkas gambar/video serah terima barang.
 * @property string|null $catatan Catatan hasil inspeksi kondisi atau kelengkapan barang.
 * @property-read string|null $bukti_foto_video_url URL publik menuju berkas bukti fisik pengembalian.
 * @property-read Peminjaman $peminjaman Relasi transaksi peminjaman terkait (N:1).
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
     * Accessor untuk mendapatkan URL publik lengkap berkas bukti fisik pengembalian (foto/video).
     * Menggunakan helper PublicUploadStorage untuk mendukung penyimpanan lokal maupun publik.
     *
     * @return string|null URL publik berkas bukti atau null jika berkas tidak dilampirkan.
     */
    public function getBuktiFotoVideoUrlAttribute(): ?string
    {
        return app(\App\Services\PublicUploadStorage::class)->url($this->bukti_foto_video);
    }

    /**
     * Relasi: Setiap data pengembalian merujuk tepat ke satu permohonan peminjaman (N:1).
     *
     * @return BelongsTo Relasi belongs-to ke model Peminjaman berdasarkan foreign key kode_pinjam.
     */
    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'kode_pinjam', 'kode_pinjam');
    }
}
