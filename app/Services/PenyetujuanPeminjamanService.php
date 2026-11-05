<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Models\Penyetujuan;
use App\Models\StaffSarana;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service PenyetujuanPeminjamanService
 *
 * Mengelola eksekusi persetujuan atau penolakan permohonan pinjam secara aman dan atomik.
 * Menggunakan transaksi basis data (`DB::transaction`) dan penguncian baris (`lockForUpdate`)
 * untuk mencegah kondisi perlombaan (race condition) ketika dua petugas sarana
 * mencoba memproses peminjaman yang sama pada detik yang bersamaan.
 */
class PenyetujuanPeminjamanService
{
    /**
     * Memproses keputusan verifikasi peminjaman (setujui / tolak) dan mencatat jejak audit persetujuan.
     *
     * Prasyarat:
     * - Parameter `$status` harus bernilai 'disetujui' atau 'ditolak'.
     * - Peminjaman target harus ditemukan di basis data dan masih berstatus 'menunggu'.
     *
     * Efek Samping:
     * - Mengunci baris transaksi peminjaman pada tingkat basis data hingga transaksi tuntas.
     * - Memperbarui kolom `status_pengajuan` dan `alasan_penolakan` pada tabel `peminjaman`.
     * - Membuat satu baris log keputusan baru di tabel `penyetujuan` dengan NIP staf yang bertugas.
     *
     * @param string $kodePinjam Kode unik transaksi peminjaman (misal: 'PJ-20261105-001').
     * @param StaffSarana $staff Model data staf sarana prasarana yang mengeksekusi persetujuan.
     * @param string $status Keputusan akhir yang diambil ('disetujui' atau 'ditolak').
     * @param string|null $catatan Alasan penolakan resmi atau catatan operasional tambahan.
     * @return Peminjaman|null Model Peminjaman yang telah diperbarui beserta relasinya, atau null jika data tidak berstatus menunggu.
     * @throws InvalidArgumentException Jika status keputusan tidak sesuai dengan domain nilai yang diizinkan.
     */
    public function process(
        string $kodePinjam,
        StaffSarana $staff,
        string $status,
        ?string $catatan = null
    ): ?Peminjaman {
        if (! in_array($status, ['disetujui', 'ditolak'], true)) {
            throw new InvalidArgumentException('Keputusan peminjaman harus disetujui atau ditolak.');
        }

        return DB::transaction(function () use ($kodePinjam, $staff, $status, $catatan) {
            $peminjaman = Peminjaman::where('kode_pinjam', $kodePinjam)
                ->lockForUpdate()
                ->first();

            if (! $peminjaman || $peminjaman->status_pengajuan !== 'menunggu') {
                return null;
            }

            $peminjaman->status_pengajuan = $status;
            if ($status === 'ditolak') {
                $peminjaman->alasan_penolakan = $catatan;
            }
            $peminjaman->save();

            Penyetujuan::create([
                'id_peminjaman' => $peminjaman->id_peminjaman,
                'nip' => $staff->nip,
                'status' => $status,
                'catatan' => $catatan,
            ]);

            return $peminjaman->load(['siswa', 'barang', 'akun']);
        });
    }
}
