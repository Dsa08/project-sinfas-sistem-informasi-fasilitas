<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Siswa
 * 
 * Mengelola data master siswa sekolah.
 * Terhubung dengan akun login siswa (relasi 1:1) dan riwayat peminjaman sarana.
 */
class Siswa extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'siswa';

    /**
     * Kunci utama berupa NIS (Nomor Induk Siswa).
     *
     * @var string
     */
    protected $primaryKey = 'nis';

    /**
     * Primary key non-increment.
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
     * Kolom-kolom yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nis',   // Nomor Induk Siswa unik
        'nama',  // Nama lengkap siswa
        'email', // Alamat email siswa
        'no_hp', // Nomor kontak / WhatsApp aktif siswa
    ];

    /**
     * Relasi: Data siswa memiliki satu akun login (1:1).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function akun()
    {
        return $this->hasOne(Akun::class, 'nis', 'nis');
    }
}
