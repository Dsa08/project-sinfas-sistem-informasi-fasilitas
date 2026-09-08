<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Pegawai
 * 
 * Mengelola data master guru dan tenaga kependidikan / staf sekolah.
 * Terhubung dengan akun login admin sarana atau admin sistem (relasi 1:1).
 */
class Pegawai extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'pegawai';

    /**
     * Kunci utama berupa NIP (Nomor Induk Pegawai).
     *
     * @var string
     */
    protected $primaryKey = 'nip';

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
        'nip',  // Nomor Induk Pegawai unik
        'nama', // Nama lengkap pegawai / staf
    ];

    /**
     * Relasi: Data pegawai memiliki satu akun login (1:1).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function akun()
    {
        return $this->hasOne(Akun::class, 'nip', 'nip');
    }
}
