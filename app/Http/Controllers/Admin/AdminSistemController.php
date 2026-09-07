<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use Carbon\Carbon;

class AdminSistemController extends Controller
{
    /**
     * Dashboard Admin Sistem — menampilkan statistik akun dari database.
     */
    public function dashboard()
    {
        $totalAccounts = Akun::count();

        // Hitung per role
        $totalSiswa = Akun::where('role', 'siswa')->count();
        $totalAdminSarana = Akun::where('role', 'admin_sarana')->count();
        $totalAdminSistem = Akun::where('role', 'admin_sistem')->count();

        // Akun baru bulan ini
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
     * Halaman System Settings (saat ini masih statis).
     */
    public function settings()
    {
        return view('admin.system.settings');
    }
}
