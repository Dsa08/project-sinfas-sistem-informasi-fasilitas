<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Peminjaman;
use App\Models\Notifikasi;
use App\Services\NotifikasiService;
use Carbon\Carbon;

class CekBatasWaktuPeminjaman extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinfas:cek-batas-waktu {--force : Paksa kirim notifikasi meskipun sudah pernah dikirim hari ini}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa transaksi peminjaman yang sedang aktif dan kirimkan notifikasi peringatan batas waktu secara otomatis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan transaksi peminjaman aktif...');

        // Ambil semua peminjaman yang telah disetujui dan belum selesai dikembalikan
        $activeLoans = Peminjaman::disetujui()
            ->whereDoesntHave('pengembalian', function ($q) {
                $q->whereNotNull('kondisi_barang');
            })
            ->with(['siswa', 'barang'])
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
                        $loan->siswa->nama ?? $loan->nis,
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
