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
        'role',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

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
}
