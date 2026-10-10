<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model Akun
 * 
 * Entitas utama autentikasi dan otorisasi pengguna pada aplikasi SINFAS.
 * Menyimpan identitas login dan peran aplikasi, lalu menghubungkan akun ke
 * profil siswa atau pegawai melalui foreign key pada tabel profil.
 */
class Akun extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Nama tabel di database yang dikelola model ini.
     *
     * @var string
     */
    protected $table = 'akun';

    /**
     * Kunci utama (primary key) tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_akun';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nis',           // Nomor Induk Siswa (jika role siswa)
        'nip',           // Nomor Induk Pegawai (jika role staf/guru/admin)
        'nama',          // Nama lengkap pengguna
        'nomor_kontak',  // Nomor WhatsApp / HP aktif untuk koordinasi
        'email',         // Alamat surel unik pengguna
        'role',          // siswa, pegawai, admin_sarana, atau admin_sistem
        'username',      // Username unik untuk login
        'password',      // Hash kata sandi
        'foto',          // Path relatif foto profil pengguna
        'is_active',     // Status keaktifan akun (true: aktif, false: disuspend)
        'must_change_password',
    ];

    /**
     * Atribut yang disembunyikan saat model diserialisasi ke Array/JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Konversi tipe data bawaan atribut Eloquent.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password'  => 'hashed',
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
    ];

    /**
     * Query Scope: Menyaring hanya akun yang berstatus aktif.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Profil siswa yang menggunakan akun ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'id_akun', 'id_akun');
    }

    /**
     * Profil pegawai yang menggunakan akun ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pegawai()
    {
        return $this->hasOne(Pegawai::class, 'id_akun', 'id_akun');
    }

    /**
     * Relasi: Akun memiliki banyak riwayat notifikasi sistem (1:N).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class, 'id_akun', 'id_akun');
    }

    /**
     * Accessor untuk mendapatkan URL lengkap foto profil akun.
     *
     * @return string|null
     */
    public function getFotoUrlAttribute(): ?string
    {
        return app(\App\Services\PublicUploadStorage::class)->url($this->foto);
    }

    /**
     * Helper: Memeriksa apakah akun bersangkutan memiliki role Siswa.
     *
     * @return bool
     */
    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }

    public function isPeminjam(): bool
    {
        return $this->isSiswa() || $this->isPegawai();
    }

    public function staffSarana()
    {
        return $this->hasOne(StaffSarana::class, 'id_akun', 'id_akun');
    }

    /**
     * Helper: Memeriksa apakah akun merupakan Admin (Sarana maupun Sistem).
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin_sarana', 'admin_sistem'], true);
    }

    /**
     * Helper: Memeriksa secara spesifik apakah akun adalah Admin Sarana Prasarana.
     *
     * @return bool
     */
    public function isAdminSarana(): bool
    {
        return $this->role === 'admin_sarana';
    }

    /**
     * Helper: Memeriksa secara spesifik apakah akun adalah Admin Sistem / IT.
     *
     * @return bool
     */
    public function isAdminSistem(): bool
    {
        return $this->role === 'admin_sistem';
    }

    /**
     * Accessor: Menghasilkan label nama role yang ramah pembaca untuk tampilan antarmuka.
     * Contoh: 'admin_sarana' -> 'Admin Sarana'
     *
     * @return string
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'siswa'        => 'Siswa',
            'pegawai'      => 'Pegawai',
            'admin_sarana' => 'Admin Sarana',
            'admin_sistem' => 'Admin Sistem',
            default        => 'Tidak diketahui',
        };
    }

    /**
     * Accessor: Menghasilkan string NIS atau NIP tergantung data yang tersedia.
     *
     * @return string
     */
    public function getNisNipAttribute(): string
    {
        return $this->nis ?? $this->nip ?? '-';
    }
}
