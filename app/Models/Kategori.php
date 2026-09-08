<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Kategori
 * 
 * Mengelola klasifikasi atau pengelompokan jenis sarana & prasarana
 * (misalnya: Elektronik, Olahraga, Multimedia, Peralatan Laboratorium, dsb.).
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function barang()
    {
        return $this->hasMany(Barang::class, 'id_kategori', 'id_kategori');
    }
}
