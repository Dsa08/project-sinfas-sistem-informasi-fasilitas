<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';
    protected $primaryKey = 'kode_barang';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_barang',
        'id_kategori',
        'nama_barang',
        'merk_model',
        'no_seri_pabrik',
        'ukuran_dimensi',
        'bahan',
        'tahun_pembelian',
        'jumlah_baik',
        'jumlah_kurang_baik',
        'jumlah_rusak_berat',
        'keterangan',
    ];

    protected $casts = [
        'jumlah_baik' => 'integer',
        'jumlah_kurang_baik' => 'integer',
        'jumlah_rusak_berat' => 'integer',
        'tahun_pembelian' => 'integer',
    ];

    /**
     * Relasi: Barang milik satu kategori.
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Relasi: Satu barang bisa dipinjam berkali-kali.
     */
    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class, 'kode_barang', 'kode_barang');
    }

    /**
     * Accessor: Total seluruh stok (baik + kurang baik + rusak berat).
     */
    public function getTotalStokAttribute(): int
    {
        return $this->jumlah_baik + $this->jumlah_kurang_baik + $this->jumlah_rusak_berat;
    }

    /**
     * Accessor: Status ketersediaan barang.
     * Available jika masih ada stok dalam kondisi baik.
     */
    public function getStatusAttribute(): string
    {
        return $this->jumlah_baik > 0 ? 'Available' : 'Unavailable';
    }
}
