<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Logout Route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // User Dashboard (Siswa)
    Route::get('/dashboard', function () {
        return view('user.dashboard');
    })->name('dashboard');

    // User Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Admin Dashboard (Admin Sarana)
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Admin Sistem Routes
    Route::get('/admin-sistem/dashboard', function () {
        return view('admin.system.dashboard');
    })->name('admin.sistem.dashboard');

    Route::get('/admin-sistem/accounts', function () {
        return view('admin.system.accounts');
    })->name('admin.sistem.accounts');

    Route::get('/admin-sistem/settings', function () {
        return view('admin.system.settings');
    })->name('admin.sistem.settings');
});

Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/admin-sistem', function () {
    return redirect()->route('admin.sistem.dashboard');
});

