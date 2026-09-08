<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AdminSistemController;
use App\Http\Controllers\Admin\AdminSaranaController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes — SINFAS (Sistem Informasi Fasilitas & Sarana Prasarana)
|--------------------------------------------------------------------------
| File ini memetakan seluruh routing antarmuka web aplikasi SINFAS.
| Routing dikelompokkan berdasarkan hak akses pengguna:
|  1. Guest Routes (Belum Login): Login, Registrasi, Forgot & Reset Password
|  2. Authenticated Routes (Sudah Login): Logout, Manajemen Profil
|  3. Role: Siswa (User Biasa): Katalog Barang, Peminjaman, Pengembalian, Status
|  4. Role: Admin Sarana: Dashboard Analitik, Master Barang, Kategori, Verifikasi
|  5. Role: Admin Sistem: Dashboard Kontrol, Konfigurasi Sistem, Manajemen Akun
|--------------------------------------------------------------------------
*/

// Redirect root URL langsung ke halaman login aplikasi
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| 1. Guest Routes (Khusus Pengguna yang Belum Terautentikasi)
|--------------------------------------------------------------------------
| Mencegah pengguna yang sudah login mengakses kembali halaman autentikasi.
*/
Route::middleware('guest')->group(function () {
    // --- Autentikasi Masuk (Login) ---
    // Menampilkan halaman form login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    // Memproses data login (mendukung Username, Email, NIS, atau NIP + Rate Limiting)
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // --- Pendaftaran Akun Siswa Baru (Registrasi) ---
    // Menampilkan formulir pendaftaran khusus siswa
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    // Memproses pendaftaran siswa baru ke tabel 'siswa' dan 'akun'
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // --- Pemulihan Kata Sandi (Forgot & Reset Password) ---
    // Menampilkan form permohonan tautan reset password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    // Mengirim tautan token reset password ke email yang terdaftar
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    // Menampilkan form pengisian password baru dengan validasi token
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    // Memproses pembaharuan kata sandi baru ke database
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| 2. Authenticated Routes (Khusus Pengguna yang Sudah Login)
|--------------------------------------------------------------------------
| Seluruh endpoint di bawah grup ini mewajibkan pengguna lolos middleware 'auth'.
*/
Route::middleware('auth')->group(function () {
    // --- Keluar Aplikasi (Logout) ---
    // Menghapus session login, meregenerasi token CSRF, dan mengarahkan kembali ke login
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- Manajemen Profil Pengguna (Dapat diakses oleh semua role) ---
    // Menampilkan biodata diri profil pengguna yang sedang login
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    // Memperbarui data umum profil (nama, email, no_hp, dll.)
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Memperbarui kata sandi mandiri (dengan verifikasi password lama)
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    /*
    |----------------------------------------------------------------------
    | 3. Fitur Siswa (Role: 'siswa')
    |----------------------------------------------------------------------
    | Mengatur fitur operasional peminjaman dan pengembalian sarana prasarana.
    */
    Route::middleware('role:siswa')->group(function () {
        // Dashboard Siswa: Menampilkan katalog sarana yang siap dipinjam & filter pencarian
        Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
        
        // Status Pengajuan: Melihat riwayat peminjaman, tracking status (menunggu, disetujui, ditolak, selesai)
        Route::get('/loan-status', [UserController::class, 'loanStatus'])->name('loan.status');
        
        // Form Pengajuan Pinjam: Menampilkan rincian barang dan form input peminjaman
        Route::get('/loan-request/{kode}', [UserController::class, 'loanRequest'])->name('loan.request');
        // Submit Pinjaman: Memproses transaksi pinjam (Dibatasi throttle anti-spam: 3 request/menit)
        Route::post('/loan-request/{kode}', [UserController::class, 'submitLoanRequest'])
            ->middleware('throttle:3,1')
            ->name('loan.submit');
            
        // Form Pengembalian: Menampilkan formulir input kondisi saat mengembalikan barang
        Route::get('/loan-return/{kode}', [UserController::class, 'loanReturn'])->name('loan.return');
        // Submit Pengembalian: Mengajukan konfirmasi pengembalian barang ke admin sarana
        Route::post('/loan-return/{kode}', [UserController::class, 'submitReturnRequest'])->name('loan.return.submit');
    });

    /*
    |----------------------------------------------------------------------
    | 4. Fitur Admin Sarana (Role: 'admin_sarana')
    |----------------------------------------------------------------------
    | Mengatur inventaris sarana fisik, stok kondisi, kategori, dan verifikasi permohonan.
    */
    Route::middleware('role:admin_sarana')->group(function () {
        // Dashboard Admin Sarana: Statistik total sarana, status peminjaman, dan visualisasi Chart.js
        Route::get('/admin/dashboard', [AdminSaranaController::class, 'dashboard'])->name('admin.dashboard');

        // --- Manajemen Master Barang (CRUD Sarana Prasarana) ---
        Route::get('/admin/items', [AdminSaranaController::class, 'items'])->name('admin.items');
        Route::post('/admin/items', [AdminSaranaController::class, 'storeItem'])->name('admin.items.store');
        Route::get('/admin/items/{kode}', [AdminSaranaController::class, 'showItem'])->name('admin.items.show');
        Route::put('/admin/items/{kode}', [AdminSaranaController::class, 'updateItem'])->name('admin.items.update');
        Route::delete('/admin/items/{kode}', [AdminSaranaController::class, 'destroyItem'])->name('admin.items.destroy');

        // --- Manajemen Master Kategori Barang (CRUD Kategori) ---
        Route::get('/admin/categories', [AdminSaranaController::class, 'categories'])->name('admin.categories');
        Route::post('/admin/categories', [AdminSaranaController::class, 'storeCategory'])->name('admin.categories.store');
        Route::put('/admin/categories/{id}', [AdminSaranaController::class, 'updateCategory'])->name('admin.categories.update');
        Route::delete('/admin/categories/{id}', [AdminSaranaController::class, 'destroyCategory'])->name('admin.categories.destroy');

        // --- Alur Verifikasi & Pengembalian Sarana ---
        // Menampilkan daftar antrean permohonan pinjam & antrean pengembalian
        Route::get('/admin/verifications', [AdminSaranaController::class, 'verifications'])->name('admin.verifications');
        // Menyetujui peminjaman: memotong stok barang layak pakai
        Route::post('/admin/verifications/{kode}/approve', [AdminSaranaController::class, 'approveRequest'])->name('admin.verifications.approve');
        // Menolak peminjaman: menyimpan alasan penolakan
        Route::post('/admin/verifications/{kode}/reject', [AdminSaranaController::class, 'rejectRequest'])->name('admin.verifications.reject');
        // Mengonfirmasi pengembalian barang: memeriksa fisik, mencatat kondisi, & mengembalikan stok
        Route::post('/admin/verifications/{kode}/confirm-return', [AdminSaranaController::class, 'confirmReturn'])->name('admin.verifications.confirm-return');
    });

    /*
    |----------------------------------------------------------------------
    | 5. Fitur Admin Sistem (Role: 'admin_sistem')
    |----------------------------------------------------------------------
    | Bertanggung jawab atas integritas sistem, konfigurasi, dan manajemen akun pengguna.
    */
    Route::middleware('role:admin_sistem')->prefix('admin-sistem')->group(function () {
        // Dashboard Admin Sistem: Metrik akun, log aktivitas sistem, dan health check
        Route::get('/dashboard', [AdminSistemController::class, 'dashboard'])->name('admin.sistem.dashboard');
        // Halaman pengaturan umum dan konfigurasi aplikasi
        Route::get('/settings', [AdminSistemController::class, 'settings'])->name('admin.sistem.settings');

        // --- Manajemen Master Akun Pengguna (CRUD Accounts) ---
        Route::get('/accounts', [AccountController::class, 'index'])->name('admin.sistem.accounts');
        Route::post('/accounts', [AccountController::class, 'store'])->name('admin.sistem.accounts.store');
        Route::get('/accounts/{id}', [AccountController::class, 'show'])->name('admin.sistem.accounts.show');
        Route::put('/accounts/{id}', [AccountController::class, 'update'])->name('admin.sistem.accounts.update');
        Route::delete('/accounts/{id}', [AccountController::class, 'destroy'])->name('admin.sistem.accounts.destroy');
        // Reset password darurat akun oleh administrator sistem
        Route::post('/accounts/{id}/reset-password', [AccountController::class, 'resetPassword'])->name('admin.sistem.accounts.reset');
    });
});

/*
|--------------------------------------------------------------------------
| Navigasi Alias & Redirect Pintas
|--------------------------------------------------------------------------
*/
// Redirect alias /admin langsung ke dashboard admin sarana
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});

// Redirect alias /admin-sistem langsung ke dashboard admin sistem
Route::get('/admin-sistem', function () {
    return redirect()->route('admin.sistem.dashboard');
});

/*
|--------------------------------------------------------------------------
| Route Fallback Penyajian File Berkas Upload
|--------------------------------------------------------------------------
| Menyajikan file foto barang yang tersimpan di folder public/uploads/ jika
| dipanggil melalui URL path /storage/uploads/.
*/
Route::get('/storage/uploads/{any}', function ($any) {
    $path = public_path('uploads/' . $any);
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
})->where('any', '.*');
