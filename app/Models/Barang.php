<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Barang
 * 
 * Merepresentasikan data inventaris fisik sarana & prasarana sekolah.
 * Mengelola informasi detail barang, kategori, riwayat peminjaman, serta
 * pembagian kuantitas berdasarkan kondisi fisik (baik, kurang baik, rusak berat).
 *
 * @property string $kode_barang Kode inventaris unik sarana (kunci utama, misal: 'AUD-001').
 * @property int $id_kategori Foreign key merujuk ke tabel kategori.
 * @property string $nama_barang Nama umum barang atau alat sarana.
 * @property string|null $merk_model Merk produsen dan tipe model barang.
 * @property string|null $no_seri_pabrik Nomor seri pabrikasi untuk pelacakan fisik.
 * @property string|null $ukuran_dimensi Dimensi fisik alat (contoh: '8 x 3 x 2 cm').
 * @property string|null $bahan Material penyusun barang (contoh: 'Plastik ABS', 'Besi').
 * @property int|null $tahun_pembelian Tahun kalender pengadaan sarana.
 * @property int $jumlah_baik Kuantitas unit dalam kondisi prima (siap dipinjam).
 * @property int $jumlah_kurang_baik Kuantitas unit aus yang membutuhkan perawatan berkala.
 * @property int $jumlah_rusak_berat Kuantitas unit rusak permanen (tidak dapat dipinjam).
 * @property string|null $keterangan Catatan operasional sarana.
 * @property string|null $foto Path relatif penyimpanan gambar sarana.
 * @property-read string|null $foto_url URL publik menuju berkas gambar barang.
 * @property-read int $total_stok Akumulasi total kuantitas dari seluruh kondisi fisik.
 * @property-read string $status Status ketersediaan sarana ('Available' / 'Unavailable').
 * @property-read Kategori $kategori Relasi kategori barang (N:1).
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Peminjaman> $peminjaman Riwayat transaksi peminjaman (1:N).
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
     * @return BelongsTo Relasi belongs-to ke model Kategori.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Accessor untuk mendapatkan URL publik lengkap berkas gambar sarana.
     * Menggunakan helper PublicUploadStorage untuk mendukung penempatan penyimpanan lokal maupun publik.
     *
     * @return string|null URL publik gambar barang atau null jika tidak ada berkas foto.
     */
    public function getFotoUrlAttribute(): ?string
    {
        return app(\App\Services\PublicUploadStorage::class)->url($this->foto);
    }

    /**
     * Relasi: Satu barang memiliki banyak riwayat transaksi peminjaman (1:N).
     *
     * @return HasMany Relasi has-many ke model Peminjaman.
     */
    public function peminjaman(): HasMany
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
