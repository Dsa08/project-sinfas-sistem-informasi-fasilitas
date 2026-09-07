<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';
    protected $primaryKey = 'kode_pinjam';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_pinjam',
        'nis',
        'kode_barang',
        'tanggal_pinjam',
        'keterangan_penggunaan',
        'lokasi_penggunaan',
        'status_pengajuan',
        'alasan_penolakan',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
    ];

    /**
     * Relasi: Peminjaman milik satu siswa.
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }

    /**
     * Relasi: Peminjaman merujuk ke satu barang.
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    /**
     * Relasi: Satu peminjaman maksimal satu pengembalian.
     */
    public function pengembalian()
    {
        return $this->hasOne(Pengembalian::class, 'kode_pinjam', 'kode_pinjam');
    }

    /**
     * Scope: hanya peminjaman yang menunggu verifikasi.
     */
    public function scopeMenunggu($query)
    {
        return $query->where('status_pengajuan', 'menunggu');
    }

    /**
     * Scope: hanya peminjaman yang sudah disetujui.
     */
    public function scopeDisetujui($query)
    {
        return $query->where('status_pengajuan', 'disetujui');
    }
}
