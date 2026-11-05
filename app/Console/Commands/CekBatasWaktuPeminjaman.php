<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Peminjaman;
use App\Models\Notifikasi;
use App\Services\NotifikasiService;
use Carbon\Carbon;

/**
 * Console Command CekBatasWaktuPeminjaman
 *
 * Perintah Artisan penjadwalan (cron job) yang memindai seluruh transaksi peminjaman aktif
 * yang telah melampaui batas waktu pinjam standar (3 hari kalender).
 * Mengirimkan notifikasi peringatan in-app dan Web Push otomatis kepada peminjam.
 */
class CekBatasWaktuPeminjaman extends Command
{
    /**
     * Nama dan format opsi perintah CLI Artisan.
     *
     * @var string
     */
    protected $signature = 'sinfas:cek-batas-waktu {--force : Paksa kirim notifikasi meskipun sudah pernah dikirim hari ini}';

    /**
     * Deskripsi ringkas tugas perintah pada daftar `php artisan list`.
     *
     * @var string
     */
    protected $description = 'Periksa transaksi peminjaman yang sedang aktif dan kirimkan notifikasi peringatan batas waktu secara otomatis';

    /**
     * Mengeksekusi pemindaian basis data dan mengirimkan peringatan keterlambatan ke peminjam.
     *
     * Efek Samping:
     * - Menyimpan baris notifikasi peringatan baru di tabel `notifikasi`.
     * - Memicu Web Push notification ke browser peminjam.
     *
     * @return int Kode status keberhasilan perintah console (0 untuk sukses).
     */
    public function handle()
    {
        $this->info('Memulai pengecekan transaksi peminjaman aktif...');

        // Ambil semua peminjaman yang telah disetujui dan belum selesai dikembalikan
        $activeLoans = Peminjaman::disetujui()
            ->whereDoesntHave('pengembalian', function ($q) {
                $q->whereNotNull('kondisi_barang');
            })
            ->with(['akun', 'siswa', 'barang'])
            ->get();

        if ($activeLoans->isEmpty()) {
            $this->info('Tidak ada transaksi peminjaman aktif yang perlu diperiksa.');
            return Command::SUCCESS;
        }

        $countNotified = 0;
        $force = $this->option('force');

        foreach ($activeLoans as $loan) {
            $tglPinjam = $loan->tanggal_pinjam ? Carbon::parse($loan->tanggal_pinjam)->startOfDay() : null;
            if (!$tglPinjam) {
                continue;
            }

            $hariBerlalu = (int) $tglPinjam->diffInDays(now()->startOfDay());

            // Hanya ingatkan jika sudah berjalan minimal 1 hari sejak tanggal pinjam
            if ($hariBerlalu >= 1) {
                // Cek apakah notifikasi tipe 'batas_waktu' untuk transaksi ini sudah dikirim hari ini
                $alreadySentToday = Notifikasi::where('kode_pinjam', $loan->kode_pinjam)
                    ->where('tipe', 'batas_waktu')
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                if (!$alreadySentToday || $force) {
                    NotifikasiService::peringatanBatasWaktu($loan, $hariBerlalu);
                    $countNotified++;

                    $this->line(sprintf(
                        '[NOTIFIKASI] Kode: %s | Peminjam: %s | Barang: %s | Telah dipinjam: %d hari',
                        $loan->kode_pinjam,
                        $loan->peminjam_nama,
                        $loan->barang->nama_barang ?? '-',
                        $hariBerlalu
                    ));
                }
            }
        }

        $this->info("Selesai! {$countNotified} notifikasi peringatan batas waktu berhasil dibuat/dikirim.");

        return Command::SUCCESS;
    }
}
