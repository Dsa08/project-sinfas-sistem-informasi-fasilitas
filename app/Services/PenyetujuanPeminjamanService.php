<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Models\Penyetujuan;
use App\Models\StaffSarana;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Menjaga perubahan status pinjaman dan jejak persetujuannya tetap atomik.
 */
class PenyetujuanPeminjamanService
{
    /**
     * Proses keputusan hanya jika pinjaman masih menunggu saat baris dikunci.
     * Penguncian mencegah dua petugas memutuskan pinjaman yang sama bersamaan.
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
