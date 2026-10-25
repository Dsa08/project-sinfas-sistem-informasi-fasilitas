<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Peminjaman;
use App\Services\DatabaseBackupService;
use App\Services\SystemSettingsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controller AdminSistemController
 * 
 * Mengelola antarmuka tingkat tinggi untuk administrator sistem/IT:
 * 1. Dashboard Ringkasan Sistem: Memantau metrik total akun pengguna, distribusi per role,
 *    pertumbuhan akun baru, dan pemantauan status pencadangan sistem.
 * 2. Konfigurasi Sistem: Mengatur jam operasional, pencadangan & pemulihan database riil,
 *    serta menampilkan riwayat aktivitas sistem terkini.
 */
class AdminSistemController extends Controller
{
    /**
     * Menampilkan dashboard utama administrator sistem.
     * Mengumpulkan metrik akun pengguna dari tabel 'akun' dan status backup.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // 1. Total seluruh akun yang terdaftar
        $totalAccounts = Akun::count();

        // 2. Distribusi pengguna berdasarkan hak akses (role)
        $totalSiswa = Akun::where('role', 'siswa')->count();
        $totalPegawai = Akun::where('role', 'pegawai')->count();
        $totalAdminSarana = Akun::where('role', 'admin_sarana')->count();
        $totalAdminSistem = Akun::where('role', 'admin_sistem')->count();

        // 3. Pertumbuhan akun baru pada bulan kalender berjalan
        $newAccountsThisMonth = Akun::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $currentMonthName = Carbon::now()->translatedFormat('F Y');

        // 4. Data Pencadangan Terakhir
        $rawLastBackup = SystemSettingsService::get('last_backup_at');
        if ($rawLastBackup) {
            $lastBackupAt = Carbon::parse($rawLastBackup)->translatedFormat('d M Y, H:i') . ' WIB';
            $lastBackupFile = SystemSettingsService::get('last_backup_file', 'sinfas_backup.sql');
            $lastBackupSubtext = 'File: ' . $lastBackupFile;
        } else {
            $lastBackupAt = '–';
            $lastBackupSubtext = 'Belum ada data cadangan';
        }

        return view('admin.system.dashboard', compact(
            'totalAccounts',
            'totalSiswa',
            'totalPegawai',
            'totalAdminSarana',
            'totalAdminSistem',
            'newAccountsThisMonth',
            'currentMonthName',
            'lastBackupAt',
            'lastBackupSubtext'
        ));
    }

    /**
     * Menampilkan halaman konfigurasi dan pengaturan sistem aplikasi.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $startTime = SystemSettingsService::get('start_time', '07:00');
        $endTime = SystemSettingsService::get('end_time', '16:00');

        $rawLastBackup = SystemSettingsService::get('last_backup_at');
        $lastBackupFormatted = $rawLastBackup 
            ? Carbon::parse($rawLastBackup)->translatedFormat('d M Y, H:i') . ' WIB'
            : 'Belum pernah dicadangkan';

        // Mengumpulkan Riwayat Aktivitas Nyata
        $activityLogs = $this->aggregateActivityLogs();

        return view('admin.system.settings', compact(
            'startTime',
            'endTime',
            'lastBackupFormatted',
            'activityLogs'
        ));
    }

    /**
     * Menyimpan preferensi jam operasional layanan sarana.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateHours(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
        ], [
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'start_time.date_format' => 'Format waktu mulai harus HH:MM (contoh: 07:00).',
            'end_time.required' => 'Waktu selesai wajib diisi.',
            'end_time.date_format' => 'Format waktu selesai harus HH:MM (contoh: 16:00).',
            'end_time.after' => 'Waktu selesai harus lebih lambat dari waktu mulai.',
        ]);

        SystemSettingsService::set('start_time', $request->start_time);
        SystemSettingsService::set('end_time', $request->end_time);

        $adminName = Auth::user()->nama ?? 'Admin Sistem';
        SystemSettingsService::logActivity(
            $adminName,
            "Memperbarui jam operasional sistem menjadi {$request->start_time} - {$request->end_time} WIB"
        );

        return redirect()->route('admin.sistem.settings')
            ->with('toast_title', 'Jam Operasional Disimpan')
            ->with('toast_message', "Jam operasional berhasil diatur menjadi {$request->start_time} - {$request->end_time} WIB.")
            ->with('success', 'Jam operasional berhasil disimpan.');
    }

    /**
     * Membuat file backup dump database SQL dan mengunduhnya ke browser administrator.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function backup()
    {
        try {
            $filePath = DatabaseBackupService::createBackup();
            $fileName = basename($filePath);

            $adminName = Auth::user()->nama ?? 'Admin Sistem';
            SystemSettingsService::logActivity(
                $adminName,
                "Pencadangan database sistem berhasil diselesaikan ({$fileName})"
            );

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/sql',
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal membuat backup database.', ['exception' => $e]);

            return redirect()->route('admin.sistem.settings')
                ->with('toast_type', 'error')
                ->with('toast_title', 'Gagal Membuat Cadangan')
                ->with('toast_message', 'Terjadi kesalahan teknis saat mengekspor database: ' . $e->getMessage())
                ->with('error', 'Gagal membuat cadangan database.');
        }
    }

    /**
     * Memulihkan data database dari file dump SQL yang diunggah.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:51200', // Maks 50MB
        ], [
            'backup_file.required' => 'Pilih file backup SQL yang ingin dipulihkan.',
            'backup_file.file'     => 'File yang diunggah tidak valid.',
            'backup_file.max'      => 'Ukuran file cadangan maksimal 50MB.',
        ]);

        $file = $request->file('backup_file');
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext !== 'sql') {
            return back()->with('error', 'Hanya file berekstensi .sql yang didukung untuk pemulihan database.');
        }

        try {
            DatabaseBackupService::restoreFromSql($file->getRealPath());

            $adminName = Auth::user()->nama ?? 'Admin Sistem';
            SystemSettingsService::logActivity(
                $adminName,
                "Pemulihan database sistem berhasil dijalankan dari file: {$file->getClientOriginalName()}"
            );

            return redirect()->route('admin.sistem.settings')
                ->with('toast_title', 'Pemulihan Database Berhasil')
                ->with('toast_message', 'Struktur dan data database SINFAS berhasil dipulihkan.')
                ->with('success', 'Pemulihan database berhasil diselesaikan.');
        } catch (\Throwable $e) {
            Log::error('Gagal memulihkan database dari SQL.', ['exception' => $e]);

            return back()->with('toast_type', 'error')
                ->with('toast_title', 'Gagal Memulihkan Database')
                ->with('toast_message', 'Terjadi kesalahan saat memproses file SQL: ' . $e->getMessage())
                ->with('error', 'Gagal memulihkan database.');
        }
    }

    /**
     * Menggabungkan log aktivitas sistem dari database nyata dan aksi sistem internal.
     *
     * @return array
     */
    protected function aggregateActivityLogs(): array
    {
        $logs = [];

        // 1. Ambil log kustom internal
        $customLogs = SystemSettingsService::get('custom_logs', []);
        foreach ($customLogs as $item) {
            $logs[] = [
                'user'      => $item['user'] ?? 'Admin Sistem',
                'action'    => $item['action'] ?? '-',
                'timestamp' => $item['timestamp'] ?? '-',
                'time'      => isset($item['created_at']) ? Carbon::parse($item['created_at']) : Carbon::now(),
            ];
        }

        // 2. Ambil pendaftaran akun terbaru (5 akun terakhir)
        $recentAccounts = Akun::latest('created_at')->take(5)->get();
        foreach ($recentAccounts as $acc) {
            $roleLabel = match ($acc->role) {
                'siswa' => 'Siswa',
                'pegawai' => 'Pegawai',
                'admin_sarana' => 'Admin Sarana',
                'admin_sistem' => 'Admin Sistem',
                default => ucfirst($acc->role),
            };

            $logs[] = [
                'user'      => 'Sistem Registrasi',
                'action'    => "Pendaftaran akun baru: {$acc->nama} ({$roleLabel})",
                'timestamp' => $acc->created_at ? $acc->created_at->translatedFormat('d M Y, H:i') . ' WIB' : '-',
                'time'      => $acc->created_at ?? Carbon::now()->subDays(1),
            ];
        }

        // 3. Ambil aktivitas peminjaman terbaru (5 peminjaman terakhir)
        $recentLoans = Peminjaman::with(['barang', 'akun'])->latest('created_at')->take(5)->get();
        foreach ($recentLoans as $loan) {
            $borrower = $loan->akun->nama ?? 'Pengguna';
            $itemName = $loan->barang->nama_barang ?? 'Alat';
            $statusText = match ($loan->status_pengajuan) {
                'disetujui' => 'disetujui',
                'ditolak' => 'ditolak',
                'dibatalkan' => 'dibatalkan oleh peminjam',
                default => 'diajukan (menunggu)',
            };

            $logs[] = [
                'user'      => $borrower,
                'action'    => "Peminjaman alat [{$itemName}] status: {$statusText}",
                'timestamp' => $loan->created_at ? $loan->created_at->translatedFormat('d M Y, H:i') . ' WIB' : '-',
                'time'      => $loan->created_at ?? Carbon::now()->subDays(2),
            ];
        }

        // Urutkan berdasarkan waktu paling baru
        usort($logs, fn($a, $b) => $b['time'] <=> $a['time']);

        // Kembalikan 10 log paling relevan
        return array_slice($logs, 0, 10);
    }
}
