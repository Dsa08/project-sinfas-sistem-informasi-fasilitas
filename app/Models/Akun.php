<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model Akun
 * 
 * Entitas utama autentikasi dan otorisasi pengguna pada aplikasi SINFAS.
 * Menyimpan identitas kredensial login dan peran aplikasi (siswa, pegawai, admin_sarana, admin_sistem),
 * lalu menghubungkan akun ke profil data master (Siswa, Pegawai, atau StaffSarana) sesuai skema ERD 2.
 *
 * @property int $id_akun Kunci utama unik akun pengguna.
 * @property string $nama Nama lengkap pengguna.
 * @property string|null $nomor_kontak Nomor telepon / WhatsApp aktif.
 * @property string|null $email Alamat surel unik akun.
 * @property string $role Peran otorisasi ('siswa', 'pegawai', 'admin_sarana', 'admin_sistem').
 * @property string $username Username unik untuk autentikasi.
 * @property string $password Hash kata sandi terenkripsi (Bcrypt).
 * @property string|null $foto Path berkas foto profil avatar.
 * @property bool $is_active Status keaktifan akun (true: aktif, false: disuspend).
 * @property bool $must_change_password Penanda kewajiban mengganti kata sandi sementara.
 * @property-read string|null $foto_url URL publik lengkap menuju berkas foto avatar.
 * @property-read string $role_label Label nama peran ramah pengguna.
 * @property-read string|null $nis Nomor Induk Siswa jika akun berperan siswa.
 * @property-read string|null $nip Nomor Induk Pegawai jika akun berperan pegawai atau admin sarana.
 * @property-read string $nis_nip Identitas nomor induk sekolah yang berlaku.
 * @property-read Siswa|null $siswa Relasi profil data siswa (1:1).
 * @property-read Pegawai|null $pegawai Relasi profil data pegawai (1:1).
 * @property-read StaffSarana|null $staffSarana Relasi profil staf sarana (1:1).
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
     * Profil data master siswa yang menggunakan akun ini (relasi 1:1).
     *
     * @return HasOne Relasi has-one ke model Siswa berdasarkan foreign key id_akun.
     */
    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class, 'id_akun', 'id_akun');
    }

    /**
     * Profil data master pegawai yang menggunakan akun ini (relasi 1:1).
     *
     * @return HasOne Relasi has-one ke model Pegawai berdasarkan foreign key id_akun.
     */
    public function pegawai(): HasOne
    {
        return $this->hasOne(Pegawai::class, 'id_akun', 'id_akun');
    }

    /**
     * Relasi: Akun memiliki banyak riwayat notifikasi sistem (1:N).
     *
     * @return HasMany Relasi has-many ke model Notifikasi.
     */
    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_akun', 'id_akun');
    }

    /**
     * Accessor untuk mendapatkan URL lengkap foto profil akun.
     * Menggunakan helper PublicUploadStorage untuk resolusi URL publik yang aman.
     *
     * @return string|null URL publik foto profil atau null jika foto belum diunggah.
     */
    public function getFotoUrlAttribute(): ?string
    {
        return app(\App\Services\PublicUploadStorage::class)->url($this->foto);
    }

    /**
     * Helper: Memeriksa apakah akun bersangkutan memiliki peran Siswa.
     *
     * @return bool True jika role bernilai 'siswa', false jika peran lain.
     */
    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    /**
     * Helper: Memeriksa apakah akun bersangkutan memiliki peran Pegawai / Guru.
     *
     * @return bool True jika role bernilai 'pegawai', false jika peran lain.
     */
    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }

    /**
     * Helper: Memeriksa apakah akun berstatus sebagai peminjam sarana yang sah (siswa atau pegawai).
     * Sesuai aturan bisnis, hanya akun siswa dan pegawai yang berhak mengajukan permohonan pinjam.
     *
     * @return bool True jika akun adalah siswa atau pegawai, false untuk admin sarana / sistem.
     */
    public function isPeminjam(): bool
    {
        return $this->isSiswa() || $this->isPegawai();
    }

    /**
     * Profil data master staf sarana yang terhubung dengan akun ini (relasi 1:1).
     *
     * @return HasOne Relasi has-one ke model StaffSarana berdasarkan foreign key id_akun.
     */
    public function staffSarana(): HasOne
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
     * Accessor: NIS siswa yang terhubung dengan akun ini (ERD 2).
     *
     * @return string|null
     */
    public function getNisAttribute(): ?string
    {
        return $this->siswa?->nis;
    }

    /**
     * Accessor: NIP pegawai atau staff sarana yang terhubung dengan akun ini (ERD 2).
     *
     * @return string|null
     */
    public function getNipAttribute(): ?string
    {
        return $this->pegawai?->nip ?? $this->staffSarana?->nip;
    }

    /**
     * Accessor: Menghasilkan string NIS atau NIP tergantung relasi profil data (ERD 2).
     *
     * @return string
     */
    public function getNisNipAttribute(): string
    {
        return $this->siswa?->nis ?? $this->pegawai?->nip ?? $this->staffSarana?->nip ?? '-';
    }
}
