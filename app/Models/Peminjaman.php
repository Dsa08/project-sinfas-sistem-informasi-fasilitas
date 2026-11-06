<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model Peminjaman
 * 
 * Mengelola siklus transaksi permohonan peminjaman sarana oleh akun peminjam (siswa atau pegawai).
 * Memuat kode transaksi unik, identitas peminjam, barang yang diajukan (kode_barang),
 * rincian lokasi dan peruntukan, status verifikasi (menunggu, disetujui, ditolak, dibatalkan),
 * hingga pencatatan alasan penolakan oleh staf sarana prasarana.
 *
 * @property string $kode_pinjam Kode transaksi unik peminjaman (kunci utama, misal: 'PJ-20261105-001').
 * @property int|null $id_peminjaman ID sekuensial peminjaman untuk relasi ERD 2.
 * @property int|null $id_akun ID akun peminjam yang terdaftar di tabel akun.
 * @property string|null $nis Nomor Induk Siswa peminjam (kompatibilitas data lama).
 * @property string $kode_barang Kode inventaris sarana yang diajukan.
 * @property \Carbon\Carbon $tanggal_pinjam Tanggal rencana dimulainya peminjaman.
 * @property string $keterangan_penggunaan Deskripsi tujuan / peruntukan peminjaman sarana.
 * @property string|null $lokasi_penggunaan Ruang kelas / laboratorium / lokasi sarana dipakai.
 * @property string $status_pengajuan Status peminjaman ('menunggu', 'disetujui', 'ditolak', 'dibatalkan').
 * @property string|null $alasan_penolakan Catatan evaluasi penolakan dari admin sarana.
 * @property-read string $peminjam_status Label peran peminjam ('Siswa', 'Pegawai', dll).
 * @property-read string $peminjam_nama Nama lengkap peminjam sarana.
 * @property-read Siswa|null $siswa Relasi profil siswa berdasarkan NIS (N:1).
 * @property-read Akun|null $akun Relasi model akun peminjam berdasarkan id_akun (N:1).
 * @property-read Barang $barang Relasi master barang sarana yang dipinjam (N:1).
 * @property-read Pengembalian|null $pengembalian Relasi bukti transaksi pengembalian (1:1).
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Penyetujuan> $penyetujuan Riwayat keputusan verifikasi admin (1:N).
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
     * Relasi: Transaksi peminjaman diajukan oleh satu data profil siswa (N:1).
     *
     * @return BelongsTo Relasi belongs-to ke model Siswa berdasarkan foreign key nis.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }

    /**
     * Relasi: Akun peminjam langsung yang terdaftar pada sistem (N:1).
     * Mencakup akun bertipe peran siswa maupun pegawai.
     *
     * @return BelongsTo Relasi belongs-to ke model Akun berdasarkan foreign key id_akun.
     */
    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_akun', 'id_akun');
    }

    /**
     * Accessor: Menghasilkan label status/peran peminjam yang konsisten dengan tabel kelola akun Admin Sistem.
     *
     * @return string Label peran peminjam ('Siswa', 'Pegawai', atau '-' jika tidak teridentifikasi).
     */
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

    /**
     * Accessor: Menghasilkan nama lengkap peminjam sarana.
     * Mengambil dari profil siswa terlebih dahulu, jika null mengambil dari nama akun pengguna.
     *
     * @return string Nama lengkap peminjam atau tanda '-' jika data tidak ditemukan.
     */
    public function getPeminjamNamaAttribute(): string
    {
        return $this->siswa?->nama ?? $this->akun?->nama ?? '-';
    }

    /**
     * Relasi: Transaksi peminjaman terkait dengan satu barang fisik sarana (N:1).
     *
     * @return BelongsTo Relasi belongs-to ke model Barang berdasarkan foreign key kode_barang.
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    /**
     * Relasi: Satu transaksi peminjaman memiliki maksimal satu catatan pengembalian fisik (1:1).
     *
     * @return HasOne Relasi has-one ke model Pengembalian berdasarkan foreign key kode_pinjam.
     */
    public function pengembalian(): HasOne
    {
        return $this->hasOne(Pengembalian::class, 'kode_pinjam', 'kode_pinjam');
    }

    /**
     * Relasi: Riwayat jejak audit keputusan verifikasi peminjaman oleh staf sarana prasarana (1:N sesuai ERD 2).
     *
     * @return HasMany Relasi has-many ke model Penyetujuan berdasarkan foreign key id_peminjaman.
     */
    public function penyetujuan(): HasMany
    {
        return $this->hasMany(Penyetujuan::class, 'id_peminjaman', 'id_peminjaman');
    }

    /**
     * Query Scope: Menyaring transaksi yang masih menunggu proses verifikasi admin sarana.
     *
     * @param Builder $query Query builder Eloquent yang sedang diproses.
     * @return Builder Query builder dengan filter status_pengajuan = 'menunggu'.
     */
    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where('status_pengajuan', 'menunggu');
    }

    /**
     * Query Scope: Menyaring transaksi peminjaman yang dimiliki oleh akun pengguna tertentu.
     *
     * @param Builder $query Query builder Eloquent yang sedang diproses.
     * @param Akun $akun Model akun pemilik transaksi.
     * @return Builder Query builder dengan filter id_akun.
     */
    public function scopeOwnedBy(Builder $query, Akun $akun): Builder
    {
        return $query->where('id_akun', $akun->id_akun);
    }

    /**
     * Query Scope: Menyaring transaksi yang telah disetujui (sarana sedang dalam masa peminjaman aktif).
     *
     * @param Builder $query Query builder Eloquent yang sedang diproses.
     * @return Builder Query builder dengan filter status_pengajuan = 'disetujui'.
     */
    public function scopeDisetujui(Builder $query): Builder
    {
        return $query->where('status_pengajuan', 'disetujui');
    }

    /**
     * Query Scope: Menyaring transaksi peminjaman yang dibatalkan langsung oleh pengguna peminjam.
     *
     * @param Builder $query Query builder Eloquent yang sedang diproses.
     * @return Builder Query builder dengan filter status_pengajuan = 'dibatalkan'.
     */
    public function scopeDibatalkan(Builder $query): Builder
    {
        return $query->where('status_pengajuan', 'dibatalkan');
    }
}
