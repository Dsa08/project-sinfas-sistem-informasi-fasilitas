<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model StaffSarana
 *
 * Mengelola data master staf operasional sarana dan prasarana (Sarpras) sekolah.
 * Berperan sebagai aktor yang melakukan verifikasi (persetujuan/penolakan) transaksi
 * peminjaman dan inspeksi kondisi fisik sarana saat dikembalikan.
 *
 * @property string $nip Nomor Induk Pegawai unik staf sarana (Primary Key non-incrementing).
 * @property int|null $id_akun ID akun autentikasi yang terhubung dengan profil staf.
 * @property string $nama Nama lengkap staf sarana prasarana.
 * @property string|null $no_hp Nomor telepon seluler / WhatsApp aktif untuk koordinasi.
 * @property-read Akun|null $akun Relasi akun login petugas.
 * @property-read Pegawai|null $pegawai Relasi data kepegawaian induk.
 */
class StaffSarana extends Model
{
    /**
     * Nama tabel basis data yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'staff_sarana';

    /**
     * Nama kolom kunci primer tabel.
     *
     * @var string
     */
    protected $primaryKey = 'nip';

    /**
     * Menandai bahwa primary key bertipe string dan tidak menggunakan auto-increment.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tipe data primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Menonaktifkan pengelolaan timestamp bawaan Laravel (created_at & updated_at).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atribut kolom yang dapat diisi secara massal (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = ['nip', 'id_akun', 'nama', 'no_hp'];

    /**
     * Mendapatkan relasi data akun login yang terhubung dengan staf sarana ini.
     *
     * @return BelongsTo Relasi belongs-to ke model Akun.
     */
    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_akun', 'id_akun');
    }

    /**
     * Mendapatkan data master kepegawaian induk yang berelasi dengan NIP staf ini.
     *
     * @return BelongsTo Relasi belongs-to ke model Pegawai.
     */
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
