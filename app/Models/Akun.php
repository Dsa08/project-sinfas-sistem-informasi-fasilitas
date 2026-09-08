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
 * Mendukung autentikasi multi-peran (Siswa, Admin Sarana, Admin Sistem)
 * serta terhubung secara polimorfis logis ke data siswa (NIS) atau pegawai (NIP).
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
        'role',          // Hak akses: 'siswa', 'admin_sarana', 'admin_sistem'
        'username',      // Username unik untuk login
        'password',      // Hash kata sandi
        'foto',          // Path relatif foto profil pengguna
        'is_active',     // Status keaktifan akun (true: aktif, false: disuspend)
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
     * Relasi: Akun terhubung ke biodata master Siswa berdasarkan NIS.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }

    /**
     * Relasi: Akun terhubung ke biodata master Pegawai/Staf berdasarkan NIP.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
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

    /**
     * Helper: Memeriksa apakah akun merupakan Admin (Sarana maupun Sistem).
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin_sarana', 'admin_sistem']);
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
            'admin_sarana' => 'Admin Sarana',
            'admin_sistem' => 'Admin Sistem',
            default        => ucfirst($this->role),
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
