<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Peminjaman;
use App\Services\DatabaseBackupService;
use App\Services\LoginAuditService;
use App\Services\PublicUploadStorage;
use App\Services\SystemSettingsService;
use App\Services\WebpImageOptimizer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

/**
 * Controller AdminSistemController
 * 
 * Mengelola antarmuka tingkat tinggi untuk administrator sistem/IT:
 * 1. Dashboard Ringkasan Sistem: Memantau metrik total akun pengguna, distribusi per role,
 *    pertumbuhan akun baru, dan pemantauan status pencadangan sistem.
 * 2. Konfigurasi Sistem: Mengatur jam operasional, pencadangan & pemulihan database riil,
 *    serta menampilkan riwayat aktivitas sistem terkini.
 * 3. Profil Admin Sistem: Pengaturan identitas biodata pribadi, foto avatar, dan ganti kata sandi mandiri.
 */
class AdminSistemController extends Controller
{
    /**
     * Menampilkan dashboard utama administrator sistem (IT/Superadmin).
     * Mengompilasi statistik total akun pengguna, pembagian peran akun, dan status pencadangan sistem.
     *
     * @return \Illuminate\View\View Tampilan view 'admin.system.dashboard' dengan metrik akun dan status backup.
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
     * Menampilkan antarmuka konfigurasi operasional sistem, utilitas backup/restore,
     * riwayat aktivitas sistem, dan audit log percobaan login terbaru.
     *
     * @return \Illuminate\View\View Tampilan view 'admin.system.settings' berisi formulir pengaturan dan log aktivitas.
     */
    public function settings()
    {
        $startTime = SystemSettingsService::get('start_time', '07:00');
        $endTime = SystemSettingsService::get('end_time', '16:00');

        $rawLastBackup = SystemSettingsService::get('last_backup_at');
        $lastBackupFormatted = $rawLastBackup 
            ? Carbon::parse($rawLastBackup)->translatedFormat('d M Y, H:i') . ' WIB'
            : 'Belum pernah dicadangkan';

        // 1. Mengumpulkan riwayat aktivitas sistem secara agregat
        $activityLogs = $this->aggregateActivityLogs();

        // 2. Mengambil 10 rekaman audit log login terbaru dari file log server
        $loginAuditLogs = LoginAuditService::getRecentLogs(10);

        return view('admin.system.settings', compact(
            'startTime',
            'endTime',
            'lastBackupFormatted',
            'activityLogs',
            'loginAuditLogs'
        ));
    }

    /**
     * Memperbarui dan memvalidasi rentang jam operasional layanan sarana prasarana sekolah.
     *
     * Prasyarat:
     * - Format jam harus HH:MM (24 jam) dan waktu selesai harus lebih lambat dari waktu mulai.
     *
     * Efek Samping:
     * - Menyimpan nilai `start_time` dan `end_time` ke file konfigurasi `system_settings.json`.
     * - Mencatat tindakan perubahan ini ke dalam riwayat aktivitas sistem internal.
     *
     * @param Request $request Request HTTP yang memuat `start_time` dan `end_time`.
     * @return \Illuminate\Http\RedirectResponse Pengalihan kembali ke halaman settings dengan pesan konfirmasi toast.
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
     * Menghasilkan berkas dump database SQL penuh dan langsung mengunduhkannya ke peramban administrator.
     *
     * Efek Samping:
     * - Membuat file cadangan baru di folder `storage/app/backups/`.
     * - Mencatat riwayat pencadangan ke log aktivitas sistem.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse Respon unduhan berkas binary SQL atau redirect jika gagal.
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
     * Memulihkan seluruh struktur tabel dan data basis data dari berkas dump SQL yang diunggah oleh admin.
     *
     * Prasyarat:
     * - Berkas yang diunggah harus bertipe SQL (`.sql`) dan berukuran maksimal 50MB.
     *
     * Efek Samping:
     * - Mengeksekusi query SQL yang menimpa basis data aktif.
     * - Mencatat riwayat pemulihan sistem ke log internal.
     *
     * @param Request $request Request HTTP yang memuat file unggahan `backup_file`.
     * @return \Illuminate\Http\RedirectResponse Pengalihan kembali ke panel pengaturan dengan status pemulihan.
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
     * Mengompilasi dan mengurutkan log aktivitas dari tiga sumber: log kustom, akun baru, dan transaksi peminjaman.
     *
     * @return array<int, array<string, mixed>> Daftar 10 riwayat aktivitas sistem paling mutakhir.
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

    /**
     * Menampilkan halaman profil pribadi khusus Admin Sistem dengan tab navigasi aktif.
     * Terpisah dari profil Siswa dan Admin Sarana untuk mencegah percampuran antarmuka antar peran.
     *
     * @param string $tab Nama tab pengaturan yang dibuka: 'profile' atau 'password'. Default 'profile'.
     * @return \Illuminate\View\View Tampilan 'admin.system.profile' dengan data akun Admin Sistem.
     */
    public function profile(string $tab = 'profile')
    {
        $allowedTabs = ['profile', 'password'];
        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'profile';
        }

        return view('admin.system.profile', [
            'user' => Auth::user(),
            'activeTab' => $tab,
        ]);
    }

    /**
     * Memperbarui informasi biodata profil Admin Sistem (nama lengkap, nomor kontak, dan email).
     *
     * Efek Samping:
     * - Memperbarui rekaman pada tabel `akun`.
     *
     * @param Request $request Permintaan HTTP berisi input 'nama', 'nomor_kontak', dan opsional 'email'.
     * @return \Illuminate\Http\RedirectResponse Pengalihan kembali ke tab profil Admin Sistem dengan pesan sukses.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nama'         => 'required|string|max:255',
            'nomor_kontak' => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255|unique:akun,email,' . $user->id_akun . ',id_akun',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.email'   => 'Format email tidak valid.',
            'email.unique'  => 'Email sudah digunakan oleh akun lain.',
        ]);

        $user->nama = $request->nama;
        $user->nomor_kontak = $request->nomor_kontak;
        if ($request->filled('email')) {
            $user->email = $request->email;
        }
        $user->save();

        return redirect()
            ->route('admin.sistem.profile', ['tab' => 'profile'])
            ->with('success', 'Profil Admin Sistem berhasil diperbarui.');
    }

    /**
     * Memperbarui kata sandi akun Admin Sistem dengan verifikasi kata sandi lama.
     *
     * Prasyarat:
     * - Kata sandi saat ini harus cocok dengan hash password pengguna yang terautentikasi.
     * - Kata sandi baru minimal 8 karakter dan mengandung kombinasi huruf serta angka.
     *
     * Efek Samping:
     * - Memperbarui kolom `password` pada tabel `akun`.
     *
     * @param Request $request Permintaan HTTP berisi 'current_password', 'new_password', dan 'new_password_confirmation'.
     * @return \Illuminate\Http\RedirectResponse Pengalihan kembali ke tab password dengan notifikasi keberhasilan atau error.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'          => 'required|string',
            'new_password'              => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
            'new_password_confirmation' => 'required|string',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required'     => 'Password baru wajib diisi.',
            'new_password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
            'new_password.min'          => 'Password baru minimal 8 karakter.',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])
                ->withInput();
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()
            ->route('admin.sistem.profile', ['tab' => 'password'])
            ->with('success', 'Kata sandi Admin Sistem berhasil diperbarui.');
    }

    /**
     * Mengunggah dan memperbarui foto avatar Admin Sistem dengan kompresi WebP.
     *
     * Efek Samping:
     * - Mengonversi gambar menjadi WebP dan menyimpannya ke disk `public_uploads`.
     * - Menghapus berkas foto lama jika tersedia.
     *
     * @param Request $request Permintaan HTTP berisi berkas gambar 'foto' (maksimal 2MB).
     * @return \Illuminate\Http\RedirectResponse Pengalihan kembali ke tab profil Admin Sistem.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'foto.required' => 'Silakan pilih berkas foto terlebih dahulu.',
            'foto.image'    => 'Berkas harus berupa gambar valid.',
            'foto.mimes'    => 'Format gambar harus berupa jpeg, png, jpg, atau webp.',
            'foto.max'      => 'Ukuran foto profil maksimal 2MB.',
        ]);

        $user = Auth::user();

        $path = app(WebpImageOptimizer::class)
            ->storeOnDisk($request->file('foto'), 'avatars', 'public_uploads', 'avatar');

        app(PublicUploadStorage::class)->delete($user->foto);

        $user->foto = $path;
        $user->save();

        return redirect()
            ->route('admin.sistem.profile', ['tab' => 'profile'])
            ->with('success', 'Foto profil Admin Sistem berhasil diperbarui.');
    }

    /**
     * Menghapus berkas foto profil Admin Sistem dan mengembalikannya ke avatar bawaan.
     *
     * Efek Samping:
     * - Menghapus berkas fisik foto dari penyimpanan server dan menyetel `foto = null`.
     *
     * @return \Illuminate\Http\RedirectResponse Pengalihan kembali ke tab profil Admin Sistem.
     */
    public function deletePhoto()
    {
        $user = Auth::user();

        app(PublicUploadStorage::class)->delete($user->foto);

        $user->foto = null;
        $user->save();

        return redirect()
            ->route('admin.sistem.profile', ['tab' => 'profile'])
            ->with('success', 'Foto profil Admin Sistem berhasil dihapus.');
    }
}
