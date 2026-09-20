<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Notifikasi;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

/**
 * Service NotifikasiService
 * 
 * Pusat logika pembuatan dan pengiriman notifikasi otomatis pada sistem SINFAS.
 * Menghandle:
 * - Pengajuan baru dari siswa -> Notifikasi ke semua Admin Sarana
 * - Verifikasi disetujui / ditolak -> Notifikasi ke Siswa peminjam
 * - Pengembalian diajukan oleh siswa -> Notifikasi ke Admin Sarana
 * - Pengembalian dikonfirmasi admin -> Notifikasi ke Siswa
 * - Peringatan batas waktu (overdue) berjalan -> Notifikasi ke Siswa (+ Email opsional)
 */
class NotifikasiService
{
    /**
     * Kirim notifikasi tunggal ke akun pengguna.
     *
     * @param  int|Akun  $akun          ID Akun atau model Akun
     * @param  string    $tipe          Tipe notifikasi
     * @param  string    $judul         Judul notifikasi
     * @param  string    $pesan         Isi pesan notifikasi
     * @param  string|null $kodePinjam  Kode transaksi peminjaman terkait
     * @param  array|null  $dataTambahan Data kontekstual tambahan (misal hari_berlalu, alasan, dll.)
     * @return Notifikasi
     */
    public static function kirim($akun, string $tipe, string $judul, string $pesan, ?string $kodePinjam = null, ?array $dataTambahan = null): Notifikasi
    {
        $idAkun = $akun instanceof Akun ? $akun->id_akun : $akun;

        return Notifikasi::create([
            'id_akun'       => $idAkun,
            'tipe'          => $tipe,
            'judul'         => $judul,
            'pesan'         => $pesan,
            'kode_pinjam'   => $kodePinjam,
            'data_tambahan' => $dataTambahan,
            'status_baca'   => false,
        ]);
    }

    /**
     * Notifikasi saat siswa mengajukan permohonan pinjaman baru.
     * Dikirimkan ke semua akun Admin Sarana aktif.
     */
    public static function pengajuanBaru(Peminjaman $peminjaman): void
    {
        $peminjaman->loadMissing(['siswa', 'barang']);
        $namaSiswa = $peminjaman->siswa->nama ?? 'Siswa (' . $peminjaman->nis . ')';
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';
        $tglPinjam = $peminjaman->tanggal_pinjam ? Carbon::parse($peminjaman->tanggal_pinjam)->format('d M Y') : date('d M Y');

        $judul = "Pengajuan Pinjaman Baru: {$namaBarang}";
        $pesan = "{$namaSiswa} mengajukan permohonan peminjaman {$namaBarang} untuk tanggal {$tglPinjam}. Kode: {$peminjaman->kode_pinjam}.";

        $adminSaranaList = Akun::where('role', 'admin_sarana')->active()->get();

        foreach ($adminSaranaList as $admin) {
            self::kirim(
                $admin->id_akun,
                'pengajuan_baru',
                $judul,
                $pesan,
                $peminjaman->kode_pinjam,
                [
                    'nis'          => $peminjaman->nis,
                    'nama_siswa'   => $namaSiswa,
                    'nama_barang'  => $namaBarang,
                    'kode_pinjam'  => $peminjaman->kode_pinjam,
                    'action_url'   => route('admin.verifications'),
                ]
            );
        }
    }

    /**
     * Notifikasi saat admin sarana menyetujui peminjaman.
     * Dikirimkan ke akun Siswa peminjam.
     */
    public static function pengajuanDisetujui(Peminjaman $peminjaman): void
    {
        $peminjaman->loadMissing(['barang']);
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';

        $akunSiswa = Akun::where('nis', $peminjaman->nis)->first();
        if (!$akunSiswa) {
            return;
        }

        $judul = "Pengajuan Disetujui: {$namaBarang}";
        $pesan = "Pengajuan peminjaman {$namaBarang} (Kode: {$peminjaman->kode_pinjam}) telah disetujui. Silakan ambil alat di Ruang Sarpras.";

        self::kirim(
            $akunSiswa->id_akun,
            'pengajuan_disetujui',
            $judul,
            $pesan,
            $peminjaman->kode_pinjam,
            [
                'kode_pinjam' => $peminjaman->kode_pinjam,
                'nama_barang' => $namaBarang,
                'status'      => 'disetujui',
            ]
        );
    }

    /**
     * Notifikasi saat admin sarana menolak permohonan peminjaman.
     * Dikirimkan ke akun Siswa peminjam beserta alasannya.
     */
    public static function pengajuanDitolak(Peminjaman $peminjaman, ?string $alasan = null): void
    {
        $peminjaman->loadMissing(['barang']);
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';

        $akunSiswa = Akun::where('nis', $peminjaman->nis)->first();
        if (!$akunSiswa) {
            return;
        }

        $judul = "Pengajuan Ditolak: {$namaBarang}";
        $alasanText = $alasan ? " Alasan: {$alasan}" : " Silakan cek status untuk detail.";
        $pesan = "Pengajuan peminjaman {$namaBarang} (Kode: {$peminjaman->kode_pinjam}) telah ditolak." . $alasanText;

        self::kirim(
            $akunSiswa->id_akun,
            'pengajuan_ditolak',
            $judul,
            $pesan,
            $peminjaman->kode_pinjam,
            [
                'kode_pinjam'      => $peminjaman->kode_pinjam,
                'nama_barang'      => $namaBarang,
                'alasan_penolakan' => $alasan,
                'status'           => 'ditolak',
            ]
        );
    }

    /**
     * Notifikasi saat siswa mengajukan form pengembalian alat.
     * Dikirimkan ke semua akun Admin Sarana aktif.
     */
    public static function pengembalianDiajukan(Peminjaman $peminjaman): void
    {
        $peminjaman->loadMissing(['siswa', 'barang']);
        $namaSiswa = $peminjaman->siswa->nama ?? 'Siswa (' . $peminjaman->nis . ')';
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';

        $judul = "Pengembalian Diajukan: {$namaBarang}";
        $pesan = "{$namaSiswa} telah mengajukan pengembalian {$namaBarang} (Kode: {$peminjaman->kode_pinjam}). Mohon verifikasi kondisi fisik.";

        $adminSaranaList = Akun::where('role', 'admin_sarana')->active()->get();

        foreach ($adminSaranaList as $admin) {
            self::kirim(
                $admin->id_akun,
                'pengembalian_diajukan',
                $judul,
                $pesan,
                $peminjaman->kode_pinjam,
                [
                    'kode_pinjam' => $peminjaman->kode_pinjam,
                    'nama_barang' => $namaBarang,
                    'action_url'  => route('admin.verifications', ['tab' => 'returns']),
                ]
            );
        }
    }

    /**
     * Notifikasi saat admin sarana memverifikasi fisik pengembalian barang.
     * Dikirimkan ke akun Siswa peminjam.
     */
    public static function pengembalianDikonfirmasi(Peminjaman $peminjaman, string $kondisi): void
    {
        $peminjaman->loadMissing(['barang']);
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';

        $akunSiswa = Akun::where('nis', $peminjaman->nis)->first();
        if (!$akunSiswa) {
            return;
        }

        $judul = "Pengembalian Dikonfirmasi: {$namaBarang}";
        $pesan = "Pengembalian {$namaBarang} (Kode: {$peminjaman->kode_pinjam}) telah diverifikasi oleh Admin dengan kondisi: {$kondisi}. Terima kasih telah menjaga fasilitas sekolah.";

        self::kirim(
            $akunSiswa->id_akun,
            'pengembalian_dikonfirmasi',
            $judul,
            $pesan,
            $peminjaman->kode_pinjam,
            [
                'kode_pinjam'    => $peminjaman->kode_pinjam,
                'nama_barang'    => $namaBarang,
                'kondisi_barang' => $kondisi,
                'status'         => 'selesai',
            ]
        );
    }

    /**
     * Notifikasi peringatan batas waktu (overdue) yang terus menghitung hari berlalu.
     * Dikirimkan ke akun Siswa peminjam.
     */
    public static function peringatanBatasWaktu(Peminjaman $peminjaman, int $hariBerlalu): void
    {
        $peminjaman->loadMissing(['barang']);
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';

        $akunSiswa = Akun::where('nis', $peminjaman->nis)->first();
        if (!$akunSiswa) {
            return;
        }

        $tglPinjamFormatted = $peminjaman->tanggal_pinjam ? Carbon::parse($peminjaman->tanggal_pinjam)->format('d M Y') : '-';
        $judul = "Peringatan Batas Waktu: {$namaBarang}";
        $pesan = "Sarana {$namaBarang} (Kode: {$peminjaman->kode_pinjam}) telah dipinjam selama {$hariBerlalu} hari sejak {$tglPinjamFormatted}. Harap segera mengembalikan sarana ke Ruang Sarpras.";

        self::kirim(
            $akunSiswa->id_akun,
            'batas_waktu',
            $judul,
            $pesan,
            $peminjaman->kode_pinjam,
            [
                'kode_pinjam'  => $peminjaman->kode_pinjam,
                'nama_barang'  => $namaBarang,
                'hari_berlalu' => $hariBerlalu,
            ]
        );

        // Fallback email notifikasi (opsional, dilindungi try-catch agar tidak menyebabkan error)
        if (!empty($akunSiswa->email)) {
            try {
                // Hanya kirim email jika konfigurasi mail aktif
                if (config('mail.mailers.smtp.host') || config('mail.default') === 'log') {
                    Mail::raw($pesan, function ($message) use ($akunSiswa, $judul) {
                        $message->to($akunSiswa->email)
                                ->subject("[SINFAS] " . $judul);
                    });
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim email notifikasi batas waktu ke {$akunSiswa->email}: " . $e->getMessage());
            }
        }
    }
}
