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
 * - Pengajuan baru & pembatalan -> notifikasi ke Admin Sarana
 * - Hasil verifikasi dan pengingat -> notifikasi ke akun peminjam
 */
class NotifikasiService
{
    /**
     * Mengirimkan satu rekaman notifikasi in-app ke pengguna dan menyalurkan Web Push ke browser aktif.
     *
     * Efek Samping:
     * - Menyimpan baris notifikasi baru ke tabel `notifikasi` di database.
     * - Memicu pengiriman pesan Web Push ke semua browser yang telah terdaftar pada akun penerima.
     *
     * @param int|Akun $akun Model Akun penerima notifikasi atau ID unik akun.
     * @param string $tipe Kategori/tipe notifikasi (misal: 'pengajuan_baru', 'pengajuan_disetujui', 'batas_waktu').
     * @param string $judul Judul singkat notifikasi untuk preview lonceng dan pop-up browser.
     * @param string $pesan Isi lengkap penjelasan pemberitahuan.
     * @param string|null $kodePinjam Kode unik transaksi peminjaman terkait (jika berhubungan dengan peminjaman).
     * @param array|null $dataTambahan Data kontekstual tambahan berupa array asosiatif (misal: 'action_url', 'alasan').
     * @return Notifikasi Model Notifikasi yang baru berhasil disimpan.
     */
    public static function kirim($akun, string $tipe, string $judul, string $pesan, ?string $kodePinjam = null, ?array $dataTambahan = null): Notifikasi
    {
        $idAkun = $akun instanceof Akun ? $akun->id_akun : $akun;
        $akunModel = $akun instanceof Akun ? $akun : Akun::find($idAkun);

        $notifikasi = Notifikasi::create([
            'id_akun'       => $idAkun,
            'tipe'          => $tipe,
            'judul'         => $judul,
            'pesan'         => $pesan,
            'kode_pinjam'   => $kodePinjam,
            'data_tambahan' => $dataTambahan,
            'status_baca'   => false,
        ]);

        if ($akunModel && ($akunModel->isPeminjam() || $akunModel->isAdminSarana())) {
            $defaultUrl = $akunModel->isAdminSarana() ? route('admin.verifications') : route('loan.status');
            app(WebPushService::class)->sendToUser(
                $akunModel,
                $judul,
                $pesan,
                $dataTambahan['action_url'] ?? $defaultUrl
            );
        }

        return $notifikasi;
    }

    /**
     * Mengirimkan notifikasi broadcast ke seluruh Admin Sarana aktif saat ada pengajuan pinjaman baru.
     *
     * Efek Samping:
     * - Menyimpan notifikasi baru ke seluruh akun dengan role `admin_sarana`.
     * - Memicu Web Push ke browser perangkat petugas Admin Sarana.
     *
     * @param Peminjaman $peminjaman Model peminjaman yang baru saja diajukan oleh siswa/pegawai.
     * @return void
     */
    public static function pengajuanBaru(Peminjaman $peminjaman): void
    {
        $peminjaman->loadMissing(['akun', 'siswa', 'barang']);
        $namaPeminjam = $peminjaman->peminjam_nama;
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';
        $tglPinjam = $peminjaman->tanggal_pinjam ? Carbon::parse($peminjaman->tanggal_pinjam)->format('d M Y') : date('d M Y');

        $judul = "Pengajuan Pinjaman Baru: {$namaBarang}";
        $pesan = "{$namaPeminjam} mengajukan permohonan peminjaman {$namaBarang} untuk tanggal {$tglPinjam}. Kode: {$peminjaman->kode_pinjam}.";

        // Mengambil daftar seluruh admin sarana aktif agar seluruh staf operasional mendapat pemberitahuan serentak
        $adminSaranaList = Akun::where('role', 'admin_sarana')->active()->get();

        foreach ($adminSaranaList as $admin) {
            self::kirim(
                $admin,
                'pengajuan_baru',
                $judul,
                $pesan,
                $peminjaman->kode_pinjam,
                [
                    'nis'          => $peminjaman->nis,
                    'id_akun'      => $peminjaman->id_akun,
                    'nama_siswa'   => $namaPeminjam,
                    'nama_barang'  => $namaBarang,
                    'kode_pinjam'  => $peminjaman->kode_pinjam,
                    'action_url'   => route('admin.verifications'),
                ]
            );
        }
    }

    /**
     * Mengirimkan notifikasi ke seluruh Admin Sarana saat peminjam membatalkan pengajuan pinjaman mandiri.
     *
     * Efek Samping:
     * - Menyimpan rekaman notifikasi pembatalan ke seluruh akun Admin Sarana aktif.
     *
     * @param Peminjaman $peminjaman Model peminjaman yang status pengajuannya dibatalkan oleh peminjam.
     * @return void
     */
    public static function pengajuanDibatalkan(Peminjaman $peminjaman): void
    {
        $peminjaman->loadMissing(['akun', 'siswa', 'barang']);
        $namaPeminjam = $peminjaman->peminjam_nama;
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';

        $judul = "Pengajuan Dibatalkan: {$namaBarang}";
        $pesan = "{$namaPeminjam} telah membatalkan permohonan peminjaman {$namaBarang} (Kode: {$peminjaman->kode_pinjam}).";

        // Mengambil seluruh admin sarana aktif untuk memperbarui antrean verifikasi mereka
        $adminSaranaList = Akun::where('role', 'admin_sarana')->active()->get();

        foreach ($adminSaranaList as $admin) {
            self::kirim(
                $admin,
                'pengajuan_dibatalkan',
                $judul,
                $pesan,
                $peminjaman->kode_pinjam,
                [
                    'id_akun'      => $peminjaman->id_akun,
                    'nama_siswa'   => $namaPeminjam,
                    'nama_barang'  => $namaBarang,
                    'kode_pinjam'  => $peminjaman->kode_pinjam,
                    'status'       => 'dibatalkan',
                    'action_url'   => route('admin.notifications'),
                ]
            );
        }
    }

    /**
     * Mengirimkan notifikasi konfirmasi persetujuan peminjaman ke akun peminjam.
     *
     * Efek Samping:
     * - Membuat notifikasi persetujuan di akun peminjam agar dapat mengambil sarana di ruang Sarpras.
     *
     * @param Peminjaman $peminjaman Model peminjaman yang telah diverifikasi dan disetujui.
     * @return void
     */
    public static function pengajuanDisetujui(Peminjaman $peminjaman): void
    {
        $peminjaman->loadMissing(['akun', 'barang']);
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';

        $akunPeminjam = $peminjaman->akun;
        if (!$akunPeminjam) {
            return;
        }

        $judul = "Pengajuan Disetujui: {$namaBarang}";
        $pesan = "Pengajuan peminjaman {$namaBarang} (Kode: {$peminjaman->kode_pinjam}) telah disetujui. Silakan ambil alat di Ruang Sarpras.";

        self::kirim(
            $akunPeminjam,
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
     * Mengirimkan notifikasi penolakan peminjaman beserta alasan resmi ke akun peminjam.
     *
     * Efek Samping:
     * - Membuat notifikasi penolakan di akun peminjam agar peminjam mengetahui alasan sarana tidak dapat dipinjam.
     *
     * @param Peminjaman $peminjaman Model peminjaman yang ditolak.
     * @param string|null $alasan Keterangan/alasan mengapa permohonan pinjam tidak disetujui.
     * @return void
     */
    public static function pengajuanDitolak(Peminjaman $peminjaman, ?string $alasan = null): void
    {
        $peminjaman->loadMissing(['akun', 'barang']);
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';

        $akunPeminjam = $peminjaman->akun;
        if (!$akunPeminjam) {
            return;
        }

        $judul = "Pengajuan Ditolak: {$namaBarang}";
        $alasanText = $alasan ? " Alasan: {$alasan}" : " Silakan cek status untuk detail.";
        $pesan = "Pengajuan peminjaman {$namaBarang} (Kode: {$peminjaman->kode_pinjam}) telah ditolak." . $alasanText;

        self::kirim(
            $akunPeminjam,
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
     * Mengirimkan notifikasi ke seluruh Admin Sarana saat peminjam telah mengisi dan mengajukan form pengembalian alat.
     *
     * Efek Samping:
     * - Menyimpan notifikasi pengembalian ke seluruh Admin Sarana agar segera memeriksa kondisi fisik sarana yang dikembalikan.
     *
     * @param Peminjaman $peminjaman Model peminjaman yang formulir pengembaliannya telah dikirim oleh peminjam.
     * @return void
     */
    public static function pengembalianDiajukan(Peminjaman $peminjaman): void
    {
        $peminjaman->loadMissing(['akun', 'siswa', 'barang']);
        $namaPeminjam = $peminjaman->peminjam_nama;
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';

        $judul = "Pengembalian Diajukan: {$namaBarang}";
        $pesan = "{$namaPeminjam} telah mengajukan pengembalian {$namaBarang} (Kode: {$peminjaman->kode_pinjam}). Mohon verifikasi kondisi fisik.";

        $adminSaranaList = Akun::where('role', 'admin_sarana')->active()->get();

        foreach ($adminSaranaList as $admin) {
            self::kirim(
                $admin,
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
     * Mengirimkan notifikasi konfirmasi penerimaan fisik sarana dan hasil inspeksi kondisi ke akun peminjam.
     *
     * Efek Samping:
     * - Memberitahu peminjam bahwa tanggung jawab peminjaman sarana telah selesai dan tercatat resmi di sistem.
     *
     * @param Peminjaman $peminjaman Model peminjaman yang pengembaliannya telah disahkan oleh staf sarana.
     * @param string $kondisi Hasil pengecekan kondisi fisik barang saat diterima ('Baik', 'Kurang Baik', atau 'Rusak Berat').
     * @return void
     */
    public static function pengembalianDikonfirmasi(Peminjaman $peminjaman, string $kondisi): void
    {
        $peminjaman->loadMissing(['akun', 'barang']);
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';

        $akunPeminjam = $peminjaman->akun;
        if (!$akunPeminjam) {
            return;
        }

        $judul = "Pengembalian Dikonfirmasi: {$namaBarang}";
        $pesan = "Pengembalian {$namaBarang} (Kode: {$peminjaman->kode_pinjam}) telah diverifikasi oleh Admin dengan kondisi: {$kondisi}. Terima kasih telah menjaga fasilitas sekolah.";

        self::kirim(
            $akunPeminjam,
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
     * Mengirimkan notifikasi peringatan keterlambatan (overdue) kepada peminjam dan fallback surel jika email terkonfigurasi.
     *
     * Efek Samping:
     * - Menyimpan notifikasi peringatan di akun peminjam.
     * - Mengirimkan email darurat via SMTP jika konfigurasi mail di server aktif.
     *
     * @param Peminjaman $peminjaman Model peminjaman yang telah melewati batas durasi peminjaman.
     * @param int $hariBerlalu Jumlah hari keterlambatan sejak tanggal jatuh tempo pengembalian.
     * @return void
     */
    public static function peringatanBatasWaktu(Peminjaman $peminjaman, int $hariBerlalu): void
    {
        $peminjaman->loadMissing(['akun', 'barang']);
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Sarana';

        $akunPeminjam = $peminjaman->akun;
        if (!$akunPeminjam) {
            return;
        }

        $tglPinjamFormatted = $peminjaman->tanggal_pinjam ? Carbon::parse($peminjaman->tanggal_pinjam)->format('d M Y') : '-';
        $judul = "Peringatan Batas Waktu: {$namaBarang}";
        $pesan = "Sarana {$namaBarang} (Kode: {$peminjaman->kode_pinjam}) telah dipinjam selama {$hariBerlalu} hari sejak {$tglPinjamFormatted}. Harap segera mengembalikan sarana ke Ruang Sarpras.";

        self::kirim(
            $akunPeminjam,
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
        if (!empty($akunPeminjam->email)) {
            try {
                // Hanya kirim email jika konfigurasi mail aktif
                if (config('mail.mailers.smtp.host') || config('mail.default') === 'log') {
                    Mail::raw($pesan, function ($message) use ($akunPeminjam, $judul) {
                        $message->to($akunPeminjam->email)
                                ->subject("[SINFAS] " . $judul);
                    });
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim email notifikasi batas waktu ke {$akunPeminjam->email}: " . $e->getMessage());
            }
        }
    }
}
