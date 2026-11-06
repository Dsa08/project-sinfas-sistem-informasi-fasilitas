<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Notifikasi
 * 
 * Mengelola entitas notifikasi sistem terpusat (In-App Notification Center).
 * Digunakan untuk merekam pemberitahuan status pengajuan (disetujui, ditolak),
 * konfirmasi pengembalian, peringatan batas waktu (overdue), dan permohonan baru untuk admin.
 *
 * @property int $id_notifikasi Kunci utama unik notifikasi.
 * @property int $id_akun ID akun pengguna penerima notifikasi.
 * @property string $tipe Kategori/jenis pemberitahuan ('pengajuan_disetujui', 'batas_waktu', dll).
 * @property string $judul Ringkasan judul notifikasi.
 * @property string $pesan Uraian pesan lengkap notifikasi.
 * @property string|null $kode_pinjam Kode transaksi peminjaman terkait jika ada.
 * @property array|null $data_tambahan Data kontekstual tambahan berupa array JSON.
 * @property bool $status_baca Status penandaan baca (false: belum dibaca, true: sudah dibaca).
 * @property \Carbon\Carbon $created_at Waktu pengiriman notifikasi.
 * @property \Carbon\Carbon $updated_at Waktu pembaruan status notifikasi.
 * @property-read int|null $hari_berlalu Jumlah hari yang telah berlalu sejak tanggal pinjam.
 * @property-read string $warna_durasi Kode warna badge peringatan durasi pinjam ('gray', 'yellow', 'red').
 * @property-read string $tipe_label Label kategori notifikasi ramah pengguna.
 * @property-read Akun $akun Relasi model akun penerima (N:1).
 * @property-read Peminjaman|null $peminjaman Relasi model transaksi peminjaman terkait (N:1).
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
     * Relasi ke akun pengguna penerima notifikasi (N:1).
     *
     * @return BelongsTo Relasi belongs-to ke model Akun berdasarkan foreign key id_akun.
     */
    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_akun', 'id_akun');
    }

    /**
     * Relasi ke data transaksi peminjaman terkait notifikasi (opsional, N:1).
     *
     * @return BelongsTo Relasi belongs-to ke model Peminjaman berdasarkan foreign key kode_pinjam.
     */
    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'kode_pinjam', 'kode_pinjam');
    }

    /**
     * Query Scope: Menyaring notifikasi yang belum dibaca (status_baca = false).
     *
     * @param Builder $query Query builder Eloquent yang sedang diproses.
     * @return Builder Query builder dengan filter status_baca = false.
     */
    public function scopeBelumDibaca(Builder $query): Builder
    {
        return $query->where('status_baca', false);
    }

    /**
     * Query Scope: Menyaring notifikasi yang sudah dibaca (status_baca = true).
     *
     * @param Builder $query Query builder Eloquent yang sedang diproses.
     * @return Builder Query builder dengan filter status_baca = true.
     */
    public function scopeDibaca(Builder $query): Builder
    {
        return $query->where('status_baca', true);
    }

    /**
     * Query Scope: Mengurutkan notifikasi terbaru di bagian paling atas (DESC berdasarkan created_at).
     *
     * @param Builder $query Query builder Eloquent yang sedang diproses.
     * @return Builder Query builder yang telah diurutkan berdasarkan tanggal dibuat menurun.
     */
    public function scopeTerbaru(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Menandai rekaman notifikasi saat ini sebagai sudah dibaca.
     *
     * Efek Samping:
     * - Memperbarui kolom `status_baca` menjadi true di basis data.
     *
     * @return bool True jika rekaman berhasil disimpan ke basis data.
     */
    public function tandaiDibaca(): bool
    {
        $this->status_baca = true;
        return $this->save();
    }

    /**
     * Menandai seluruh notifikasi yang belum dibaca milik akun tertentu menjadi sudah dibaca.
     *
     * Efek Samping:
     * - Memperbarui secara massal seluruh baris notifikasi aktif milik akun terkait menjadi status_baca = true.
     *
     * @param int $idAkun ID unik akun pengguna penerima notifikasi.
     * @return int Jumlah baris notifikasi yang berhasil diperbarui.
     */
    public static function tandaiSemuaDibaca(int $idAkun): int
    {
        return static::where('id_akun', $idAkun)
            ->where('status_baca', false)
            ->update(['status_baca' => true]);
    }

    /**
     * Accessor: Menghitung berapa hari kalender yang telah berlalu sejak tanggal peminjaman barang.
     * Digunakan untuk perhitungan analitik durasi pinjam dan penentuan status keterlambatan.
     *
     * @return int|null Jumlah hari berlalu atau null jika data transaksi peminjaman tidak terkait.
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
     * Accessor: Menghasilkan kode warna badge durasi pinjam untuk indikator antarmuka pengguna:
     * - 1 - 9 hari: Abu-abu ('gray' - masa pinjam wajar)
     * - 10 - 50 hari: Kuning ('yellow' - mendekati batas tenggat waktu)
     * - > 50 hari: Merah ('red' - telah melampaui batas waktu / overdue)
     *
     * @return string Kode warna indikator badge antarmuka ('gray', 'yellow', atau 'red').
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
     * Accessor: Menghasilkan label nama kategori notifikasi yang ramah pembaca untuk antarmuka.
     *
     * @return string Label teks kategori notifikasi (contoh: 'Disetujui', 'Peringatan Batas Waktu').
     */
    public function getTipeLabelAttribute(): string
    {
        return match ($this->tipe) {
            'pengajuan_disetujui'        => 'Disetujui',
            'pengajuan_ditolak'          => 'Ditolak',
            'pengajuan_dibatalkan'       => 'Dibatalkan',
            'batas_waktu'                => 'Peringatan Batas Waktu',
            'pengajuan_baru'             => 'Pengajuan Baru',
            'pengembalian_diajukan'      => 'Pengembalian Diajukan',
            'pengembalian_dikonfirmasi'  => 'Pengembalian Dikonfirmasi',
            default                      => 'Informasi',
        };
    }
}
