<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    use HasFactory;

    protected $table = 'pengembalian';
    protected $primaryKey = 'kode_kembali';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_kembali',
        'kode_pinjam',
        'tanggal_kembali',
        'kondisi_barang',
        'bukti_foto_video',
    ];

    protected $casts = [
        'tanggal_kembali' => 'date',
    ];

    /**
     * Relasi: Pengembalian merujuk ke satu peminjaman.
     */
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'kode_pinjam', 'kode_pinjam');
    }
}
