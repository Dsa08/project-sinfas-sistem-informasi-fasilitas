<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Siswa
 * 
 * Mengelola data master siswa sekolah pada sistem informasi SINFAS.
 * Menyimpan profil siswa (NIS, nama, kelas, kontak, email) dan menghubungkannya
 * dengan akun autentikasi pengguna (relasi N:1 atau 1:1 via foreign key id_akun).
 *
 * @property string $nis Nomor Induk Siswa unik (kunci utama sekolah).
 * @property int|null $id_akun ID akun login sistem yang terhubung.
 * @property string $nama Nama lengkap siswa.
 * @property string|null $email Alamat surel siswa.
 * @property string|null $kelas Tingkat / rombongan belajar siswa (misal: 'XII RPL 1').
 * @property string|null $no_hp Nomor telepon / WhatsApp aktif siswa.
 * @property-read Akun|null $akun Relasi akun login sistem (N:1).
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
        'id_akun',
        'nama',  // Nama lengkap siswa
        'email', // Alamat email siswa
        'kelas',
        'no_hp', // Nomor kontak / WhatsApp aktif siswa
    ];

    /**
     * Relasi: Data master siswa terhubung ke satu akun login sistem (N:1).
     *
     * @return BelongsTo Relasi belongs-to ke model Akun berdasarkan foreign key id_akun.
     */
    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_akun', 'id_akun');
    }
}
