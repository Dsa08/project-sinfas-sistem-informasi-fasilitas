<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Akun extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'akun';
    protected $primaryKey = 'id_akun';

    protected $fillable = [
        'nis',
        'nip',
        'nama',
        'nomor_kontak',
        'email',
        'role',
        'username',
        'password',
        'foto',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: hanya akun yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin_sarana', 'admin_sistem']);
    }

    /**
     * Mendapatkan label role yang human-readable
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'siswa' => 'Siswa',
            'admin_sarana' => 'Admin Sarana',
            'admin_sistem' => 'Admin Sistem',
            default => ucfirst($this->role),
        };
    }

    /**
     * Mendapatkan NIS atau NIP tergantung role
     */
    public function getNisNipAttribute(): string
    {
        return $this->nis ?? $this->nip ?? '-';
    }
}

