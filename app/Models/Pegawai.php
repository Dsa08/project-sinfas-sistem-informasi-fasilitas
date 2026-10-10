<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Pegawai
 * 
 * Mengelola data master guru dan tenaga kependidikan / staf sekolah.
 * Menyimpan profil pegawai dan relasi akun pegawai.
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
        'id_akun',
        'nama', // Nama lengkap pegawai / staf
        'email',
        'jabatan',
        'no_hp',
    ];

    /**
     * Relasi: Data pegawai memiliki satu akun login (1:1).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun', 'id_akun');
    }
}
