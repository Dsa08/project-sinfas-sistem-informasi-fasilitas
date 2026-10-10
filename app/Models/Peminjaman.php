<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Peminjaman
 * 
 * Mengelola siklus transaksi permohonan peminjaman sarana oleh akun.
 * Memuat kode transaksi, identitas akun peminjam,
 * barang yang dipinjam (kode_barang), rincian lokasi dan peruntukan, status verifikasi,
 * hingga alasan jika permohonan ditolak admin.
 */
class Peminjaman extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'peminjaman';

    /**
     * Kunci utama bertipe kode string (misal: 'PJM-20260908-ABCD').
     *
     * @var string
     */
    protected $primaryKey = 'kode_pinjam';

    /**
     * Primary key tidak auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tipe primary key string.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_pinjam',            // Kode transaksi unik peminjaman
        'id_peminjaman',
        'id_akun',
        'nis',                    // Kolom lama untuk riwayat dan kompatibilitas
        'kode_barang',            // Kode sarana prasarana yang diajukan
        'tanggal_pinjam',         // Tanggal rencana/mulai peminjaman
        'keterangan_penggunaan',  // Keperluan peminjaman (kegiatan belajar, lomba, dll.)
        'lokasi_penggunaan',      // Ruangan / area penggunaan barang
        'status_pengajuan',       // Status: 'menunggu', 'disetujui', 'ditolak', 'selesai'
        'alasan_penolakan',       // Catatan alasan dari admin bila status 'ditolak'
    ];

    /**
     * Konversi tipe data atribut Eloquent.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_pinjam' => 'date',
    ];

    /**
     * Relasi: Transaksi peminjaman diajukan oleh satu siswa (N:1).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }

    /** Akun peminjam langsung, termasuk akun pegawai. */
    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun', 'id_akun');
    }

    /** Label status diambil dari role akun agar konsisten dengan tabel akun Admin Sistem. */
    public function getPeminjamStatusAttribute(): string
    {
        if ($this->akun) {
            return $this->akun->role_label;
        }

        if (filled($this->getAttribute('nis'))) {
            return 'Siswa';
        }

        return '-';
    }

    /** Nama peminjam mengikuti data siswa yang memiliki NIS transaksi. */
    public function getPeminjamNamaAttribute(): string
    {
        return $this->siswa?->nama ?? $this->akun?->nama ?? '-';
    }

    /**
     * Relasi: Transaksi peminjaman terkait dengan satu barang fisik (N:1).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    /**
     * Relasi: Satu transaksi peminjaman memiliki maksimal satu catatan pengembalian (1:1).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pengembalian()
    {
        return $this->hasOne(Pengembalian::class, 'kode_pinjam', 'kode_pinjam');
    }

    public function penyetujuan()
    {
        return $this->hasMany(Penyetujuan::class, 'id_peminjaman', 'id_peminjaman');
    }

    /**
     * Query Scope: Menyaring transaksi yang masih menunggu proses verifikasi admin.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMenunggu($query)
    {
        return $query->where('status_pengajuan', 'menunggu');
    }

    public function scopeOwnedBy($query, Akun $akun)
    {
        return $query->where('id_akun', $akun->id_akun);
    }

    /**
     * Query Scope: Menyaring transaksi yang telah disetujui (barang sedang dipinjam).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisetujui($query)
    {
        return $query->where('status_pengajuan', 'disetujui');
    }
}
