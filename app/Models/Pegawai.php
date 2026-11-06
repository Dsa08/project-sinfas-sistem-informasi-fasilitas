<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Pegawai
 * 
 * Mengelola data master guru dan tenaga kependidikan / staf sekolah.
 * Menyimpan profil identitas pegawai (NIP, nama, jabatan, email, nomor HP)
 * dan menghubungkannya dengan akun login sistem (relasi N:1 atau 1:1 via foreign key id_akun).
 *
 * @property string $nip Nomor Induk Pegawai unik (kunci utama).
 * @property int|null $id_akun ID akun login sistem yang terhubung.
 * @property string $nama Nama lengkap pendidik / staf sekolah.
 * @property string|null $email Alamat surel kedinasan atau pribadi pegawai.
 * @property string|null $jabatan Posisi fungsional (misal: 'Guru Mata Pelajaran', 'Staff TU').
 * @property string|null $no_hp Nomor kontak aktif / WhatsApp.
 * @property-read Akun|null $akun Relasi akun login pengguna (N:1).
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
     * Relasi: Data master pegawai terhubung ke satu akun login sistem (N:1).
     *
     * @return BelongsTo Relasi belongs-to ke model Akun berdasarkan foreign key id_akun.
     */
    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_akun', 'id_akun');
    }
}
