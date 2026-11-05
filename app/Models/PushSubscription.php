<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model PushSubscription
 *
 * Menyimpan data endpoint dan kredensial kriptografis Web Push API milik browser pengguna.
 * Digunakan oleh WebPushService untuk mengirimkan notifikasi instan langsung ke perangkat
 * siswa atau admin sarana saat terjadi pembaruan status peminjaman.
 *
 * @property int $id Kunci primer unik langganan.
 * @property int $id_akun ID akun pemilik perangkat browser.
 * @property string $endpoint URL endpoint push service yang diterbitkan oleh browser vendor (Google/Mozilla/Apple).
 * @property string $endpoint_hash Hash SHA-256 dari endpoint untuk efisiensi pencarian dan pengindeksan unik.
 * @property string $public_key Kunci publik ECDH P-256 (`p256dh`) untuk enkripsi muatan pesan.
 * @property string $auth_token Rahasia autentikasi klien (`auth`) dari browser pengguna.
 * @property string|null $content_encoding Skema kompresi/enkripsi konten (misal: 'aes128gcm').
 * @property-read Akun $akun Relasi data akun pengguna pemilik langganan ini.
 */
class PushSubscription extends Model
{
    /**
     * Atribut kolom yang dapat diisi secara massal (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_akun',
        'endpoint',
        'endpoint_hash',
        'public_key',
        'auth_token',
        'content_encoding',
    ];

    /**
     * Mendapatkan relasi data akun pengguna pemilik langganan push browser ini.
     *
     * @return BelongsTo Relasi belongs-to ke model Akun.
     */
    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_akun', 'id_akun');
    }
}
