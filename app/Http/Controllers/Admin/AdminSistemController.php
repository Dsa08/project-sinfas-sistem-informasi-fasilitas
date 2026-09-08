<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use Carbon\Carbon;

/**
 * Controller AdminSistemController
 * 
 * Mengelola antarmuka tingkat tinggi untuk administrator sistem/IT:
 * 1. Dashboard Ringkasan Sistem: Memantau metrik total akun pengguna, distribusi per role
 *    (Siswa, Admin Sarana, Admin Sistem), serta pertumbuhan akun baru bulan ini.
 * 2. Konfigurasi Sistem: Mengelola parameter global dan preferensi pengaturan aplikasi SINFAS.
 */
class AdminSistemController extends Controller
{
    /**
     * Menampilkan dashboard utama administrator sistem.
     * Mengumpulkan metrik akun pengguna dari tabel 'akun'.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // 1. Total seluruh akun yang terdaftar
        $totalAccounts = Akun::count();

        // 2. Distribusi pengguna berdasarkan hak akses (role)
        $totalSiswa = Akun::where('role', 'siswa')->count();
        $totalAdminSarana = Akun::where('role', 'admin_sarana')->count();
        $totalAdminSistem = Akun::where('role', 'admin_sistem')->count();

        // 3. Pertumbuhan akun baru pada bulan kalender berjalan
        $newAccountsThisMonth = Akun::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $currentMonthName = Carbon::now()->translatedFormat('F Y');

        return view('admin.system.dashboard', compact(
            'totalAccounts',
            'totalSiswa',
            'totalAdminSarana',
            'totalAdminSistem',
            'newAccountsThisMonth',
            'currentMonthName'
        ));
    }

    /**
     * Menampilkan halaman konfigurasi dan pengaturan sistem aplikasi.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        return view('admin.system.settings');
    }
}
