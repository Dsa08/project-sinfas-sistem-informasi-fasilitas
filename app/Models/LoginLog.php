<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model LoginLog
 *
 * Mewakili catatan riwayat autentikasi pengguna pada tabel 'login_logs'.
 * Mendokumentasikan upaya login sukses maupun gagal untuk kebutuhan audit keamanan.
 *
 * @property int $id
 * @property int|null $id_akun
 * @property string $identifier
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $status ('sukses', 'gagal_password', 'gagal_akun_tidak_ditemukan', 'gagal_nonaktif', 'terkunci_rate_limit')
 * @property string|null $keterangan
 * @property \Carbon\Carbon $login_at
 * @property-read \App\Models\Akun|null $akun
 */
class LoginLog extends Model
{
    use HasFactory;

    /**
     * Nama tabel pada database.
     *
     * @var string
     */
    protected $table = 'login_logs';

    /**
     * Nonaktifkan created_at dan updated_at standar karena tabel menggunakan 'login_at'.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_akun',
        'identifier',
        'ip_address',
        'user_agent',
        'status',
        'keterangan',
        'login_at',
    ];

    /**
     * Konversi tipe data otomatis untuk atribut model.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'login_at' => 'datetime',
    ];

    /**
     * Relasi ke model Akun (jika percobaan login terkait dengan akun yang terdaftar).
     *
     * @return BelongsTo
     */
    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_akun', 'id_akun');
    }

    /**
     * Helper: Dapatkan label status ramah pengguna dalam Bahasa Indonesia.
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'sukses' => 'Berhasil Masuk',
            'gagal_password' => 'Kata Sandi Salah',
            'gagal_akun_tidak_ditemukan' => 'Akun Tidak Ditemukan',
            'gagal_nonaktif' => 'Akun Dinonaktifkan',
            'terkunci_rate_limit' => 'Terblokir Sementara (Rate Limit)',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Helper: Dapatkan class badge visual (warna indikator) untuk status login.
     *
     * @return string
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'sukses' => 'badge-success',
            'gagal_password' => 'badge-danger',
            'gagal_akun_tidak_ditemukan' => 'badge-warning',
            'gagal_nonaktif' => 'badge-danger',
            'terkunci_rate_limit' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Helper: Ringkasan nama browser dan perangkat dari string user agent.
     *
     * @return string
     */
    public function getDeviceSummaryAttribute(): string
    {
        $ua = $this->user_agent ?? '';
        if (empty($ua)) {
            return 'Perangkat Tidak Diketahui';
        }

        // Deteksi platform OS
        $platform = 'Desktop';
        if (stripos($ua, 'Android') !== false) {
            $platform = 'Android';
        } elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
            $platform = 'iOS';
        } elseif (stripos($ua, 'Windows') !== false) {
            $platform = 'Windows';
        } elseif (stripos($ua, 'Macintosh') !== false) {
            $platform = 'macOS';
        } elseif (stripos($ua, 'Linux') !== false) {
            $platform = 'Linux';
        }

        // Deteksi browser
        $browser = 'Browser';
        if (stripos($ua, 'Edg') !== false) {
            $browser = 'Edge';
        } elseif (stripos($ua, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($ua, 'Safari') !== false) {
            $browser = 'Safari';
        }

        return "{$browser} ({$platform})";
    }
}
