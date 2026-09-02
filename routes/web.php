<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AdminSistemController;

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

    // General Profile Routes (Bisa diakses seluruh user yang login)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Siswa Routes
    Route::middleware('role:siswa')->group(function () {
        Route::get('/dashboard', function () {
            return view('user.dashboard');
        })->name('dashboard');

        // Loan Request Route
        Route::get('/loan-request/{id?}', function ($id = 1) {
            $items = [
                1 => [
                    'id' => 1,
                    'name' => 'Projector Epson X300',
                    'category' => 'Electronics / Projector',
                    'condition' => 'Good',
                    'status' => 'Available',
                    'image' => 'assets/pictures/projector_sample.jpg',
                ],
                2 => [
                    'id' => 2,
                    'name' => 'Portable Speaker JBL',
                    'category' => 'Audio / Speaker',
                    'condition' => 'Good',
                    'status' => 'Available',
                    'image' => 'assets/pictures/projector_sample.jpg',
                ],
                3 => [
                    'id' => 3,
                    'name' => 'Folding Table 180cm',
                    'category' => 'Furniture / Table',
                    'condition' => 'Good',
                    'status' => 'Available',
                    'image' => 'assets/pictures/projector_sample.jpg',
                ],
                4 => [
                    'id' => 4,
                    'name' => 'Whiteboard 120cm',
                    'category' => 'Equipment / Board',
                    'condition' => 'Good',
                    'status' => 'Available',
                    'image' => 'assets/pictures/projector_sample.jpg',
                ],
            ];

            $item = $items[$id] ?? $items[1];

            return view('user.loan-request', compact('item'));
        })->name('loan.request');
    });

    // Admin Sarana Routes
    Route::middleware('role:admin_sarana')->group(function () {
        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        Route::get('/admin/items', function () {
            return view('admin.items');
        })->name('admin.items');

        Route::get('/admin/verifications', function () {
            return view('admin.verifications');
        })->name('admin.verifications');

        Route::get('/admin/categories', function () {
            return view('admin.categories');
        })->name('admin.categories');
    });

    // Admin Sistem Routes
    Route::middleware('role:admin_sistem')->prefix('admin-sistem')->group(function () {
        // Dashboard & Settings
        Route::get('/dashboard', [AdminSistemController::class, 'dashboard'])->name('admin.sistem.dashboard');
        Route::get('/settings', [AdminSistemController::class, 'settings'])->name('admin.sistem.settings');

        // CRUD Accounts
        Route::get('/accounts', [AccountController::class, 'index'])->name('admin.sistem.accounts');
        Route::post('/accounts', [AccountController::class, 'store'])->name('admin.sistem.accounts.store');
        Route::get('/accounts/{id}', [AccountController::class, 'show'])->name('admin.sistem.accounts.show');
        Route::put('/accounts/{id}', [AccountController::class, 'update'])->name('admin.sistem.accounts.update');
        Route::delete('/accounts/{id}', [AccountController::class, 'destroy'])->name('admin.sistem.accounts.destroy');
        Route::post('/accounts/{id}/reset-password', [AccountController::class, 'resetPassword'])->name('admin.sistem.accounts.reset');
    });
});

Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/admin-sistem', function () {
    return redirect()->route('admin.sistem.dashboard');
});
