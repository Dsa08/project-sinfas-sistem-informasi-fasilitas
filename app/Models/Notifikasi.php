<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model Notifikasi
 * 
 * Mengelola entitas notifikasi sistem terpusat (In-App Notification Center).
 * Digunakan untuk merekam pemberitahuan status pengajuan (disetujui, ditolak),
 * konfirmasi pengembalian, peringatan batas waktu (overdue), dan permohonan baru untuk admin.
 */
class Notifikasi extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'notifikasi';

    /**
     * Kunci utama (primary key).
     *
     * @var string
     */
    protected $primaryKey = 'id_notifikasi';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_akun',
        'tipe',
        'judul',
        'pesan',
        'kode_pinjam',
        'data_tambahan',
        'status_baca',
    ];

    /**
     * Konversi tipe data atribut Eloquent.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status_baca'   => 'boolean',
        'data_tambahan' => 'array',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * Relasi ke akun penerima notifikasi.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun', 'id_akun');
    }

    /**
     * Relasi ke data transaksi peminjaman terkait (opsional).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'kode_pinjam', 'kode_pinjam');
    }

    /**
     * Query Scope: Menyaring notifikasi yang belum dibaca.
     */
    public function scopeBelumDibaca($query)
    {
        return $query->where('status_baca', false);
    }

    /**
     * Query Scope: Menyaring notifikasi yang sudah dibaca.
     */
    public function scopeDibaca($query)
    {
        return $query->where('status_baca', true);
    }

    /**
     * Query Scope: Urutkan notifikasi terbaru di atas.
     */
    public function scopeTerbaru($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Helper: Menandai notifikasi ini sudah dibaca.
     */
    public function tandaiDibaca(): bool
    {
        $this->status_baca = true;
        return $this->save();
    }

    /**
     * Helper: Menandai seluruh notifikasi milik akun tertentu sudah dibaca.
     */
    public static function tandaiSemuaDibaca(int $idAkun): int
    {
        return static::where('id_akun', $idAkun)
            ->where('status_baca', false)
            ->update(['status_baca' => true]);
    }

    /**
     * Menghitung berapa hari berlalu sejak tanggal_pinjam transaksi.
     * Digunakan untuk pewarnaan dan perhitungan otomatis waktu berjalan.
     */
    public function getHariBerlaluAttribute(): ?int
    {
        if ($this->peminjaman && $this->peminjaman->tanggal_pinjam) {
            $tglPinjam = Carbon::parse($this->peminjaman->tanggal_pinjam)->startOfDay();
            return (int) $tglPinjam->diffInDays(now()->startOfDay());
        }

        if (!empty($this->data_tambahan['hari_berlalu'])) {
            return (int) $this->data_tambahan['hari_berlalu'];
        }

        return null;
    }

    /**
     * Menghasilkan warna badge durasi pinjam sesuai aturan:
     * - 1 - 9 hari: Abu-abu (gray)
     * - 10 - 50 hari: Kuning (yellow)
     * - > 50 hari: Merah (red)
     */
    public function getWarnaDurasiAttribute(): string
    {
        $hari = $this->hari_berlalu ?? 0;

        if ($hari >= 1 && $hari <= 9) {
            return 'gray';
        } elseif ($hari >= 10 && $hari <= 50) {
            return 'yellow';
        } elseif ($hari > 50) {
            return 'red';
        }

        return 'gray';
    }

    /**
     * Accessor label tipe notifikasi yang ramah pengguna.
     */
    public function getTipeLabelAttribute(): string
    {
        return match ($this->tipe) {
            'pengajuan_disetujui'        => 'Disetujui',
            'pengajuan_ditolak'          => 'Ditolak',
            'batas_waktu'                => 'Peringatan Batas Waktu',
            'pengajuan_baru'             => 'Pengajuan Baru',
            'pengembalian_diajukan'      => 'Pengembalian Diajukan',
            'pengembalian_dikonfirmasi'  => 'Pengembalian Dikonfirmasi',
            default                      => 'Informasi',
        };
    }
}
