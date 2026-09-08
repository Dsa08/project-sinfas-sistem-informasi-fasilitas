<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Barang
 * 
 * Merepresentasikan data inventaris fisik sarana & prasarana sekolah.
 * Mengelola informasi detail barang, kategori, riwayat peminjaman, serta
 * pembagian kuantitas berdasarkan kondisi fisik (baik, kurang baik, rusak berat).
 */
class Barang extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'barang';

    /**
     * Kunci utama berupa kode string unik (contoh: 'BRG-001').
     *
     * @var string
     */
    protected $primaryKey = 'kode_barang';

    /**
     * Primary key tidak menggunakan auto-incrementing integer.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tipe data primary key adalah string.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Kolom-kolom yang dapat diisi secara massal saat create atau update.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_barang',         // Kode inventaris unik
        'id_kategori',         // Foreign key ke tabel kategori
        'nama_barang',         // Nama umum barang / alat
        'merk_model',          // Merk dan spesifikasi tipe/model
        'no_seri_pabrik',      // Nomor seri pabrikan
        'ukuran_dimensi',      // Dimensi fisik (misal: 100x50 cm)
        'bahan',               // Material penyusun (kayu, besi, plastik, dll.)
        'tahun_pembelian',     // Tahun pengadaan sarana
        'jumlah_baik',         // Unit stok kondisi prima (layak pinjam)
        'jumlah_kurang_baik',  // Unit stok kondisi aus/butuh perawatan
        'jumlah_rusak_berat',  // Unit stok rusak total (tidak layak pakai)
        'keterangan',          // Catatan tambahan mengenai barang
        'foto',                // Path berkas gambar/foto dokumentasi barang
    ];

    /**
     * Pemetaan tipe data kolom database ke tipe data native PHP.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'jumlah_baik'        => 'integer',
        'jumlah_kurang_baik' => 'integer',
        'jumlah_rusak_berat' => 'integer',
        'tahun_pembelian'    => 'integer',
    ];

    /**
     * Relasi: Barang merupakan bagian dari satu kategori induk (N:1).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Relasi: Satu barang memiliki banyak riwayat transaksi peminjaman (1:N).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class, 'kode_barang', 'kode_barang');
    }

    /**
     * Accessor: Menghitung total akumulasi stok barang dari ketiga kondisi fisik.
     * Total = Baik + Kurang Baik + Rusak Berat
     *
     * @return int
     */
    public function getTotalStokAttribute(): int
    {
        return $this->jumlah_baik + $this->jumlah_kurang_baik + $this->jumlah_rusak_berat;
    }

    /**
     * Accessor: Menentukan status ketersediaan barang untuk dipinjam siswa.
     * Bernilai 'Available' jika stok 'jumlah_baik' > 0, selain itu 'Unavailable'.
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        return $this->jumlah_baik > 0 ? 'Available' : 'Unavailable';
    }
}
