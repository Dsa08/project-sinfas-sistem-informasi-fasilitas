<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Kategori
 * 
 * Mengelola klasifikasi atau pengelompokan jenis sarana & prasarana
 * (misalnya: Audio & Sound System, Proyektor & Presentasi, Kamera & Dokumentasi, dsb.).
 *
 * @property int $id_kategori Kunci utama auto-increment kategori.
 * @property string $nama_kategori Nama klasifikasi kategori sarana prasarana.
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Barang> $barang Daftar barang di bawah kategori ini (1:N).
 */
class Kategori extends Model
{
    use HasFactory;

    /**
     * Nama tabel pada database.
     *
     * @var string
     */
    protected $table = 'kategori';

    /**
     * Kunci utama auto-increment.
     *
     * @var string
     */
    protected $primaryKey = 'id_kategori';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_kategori', // Nama klasifikasi kategori
    ];

    /**
     * Relasi: Satu kategori dapat menaungi banyak item barang inventaris (1:N).
     *
     * @return HasMany Relasi has-many ke model Barang berdasarkan foreign key id_kategori.
     */
    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'id_kategori', 'id_kategori');
    }
}
