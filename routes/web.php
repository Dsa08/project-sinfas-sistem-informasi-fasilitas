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

    // Forgot & Reset Password Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
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
        Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
        Route::get('/loan-status', [UserController::class, 'loanStatus'])->name('loan.status');
        Route::get('/loan-request/{kode}', [UserController::class, 'loanRequest'])->name('loan.request');
        Route::post('/loan-request/{kode}', [UserController::class, 'submitLoanRequest'])
            ->middleware('throttle:3,1')
            ->name('loan.submit');
        Route::get('/loan-return/{kode}', [UserController::class, 'loanReturn'])->name('loan.return');
        Route::post('/loan-return/{kode}', [UserController::class, 'submitReturnRequest'])->name('loan.return.submit');
    });

    // Admin Sarana Routes
    Route::middleware('role:admin_sarana')->group(function () {
        // Dashboard
        Route::get('/admin/dashboard', [AdminSaranaController::class, 'dashboard'])->name('admin.dashboard');

        // Items CRUD
        Route::get('/admin/items', [AdminSaranaController::class, 'items'])->name('admin.items');
        Route::post('/admin/items', [AdminSaranaController::class, 'storeItem'])->name('admin.items.store');
        Route::get('/admin/items/{kode}', [AdminSaranaController::class, 'showItem'])->name('admin.items.show');
        Route::put('/admin/items/{kode}', [AdminSaranaController::class, 'updateItem'])->name('admin.items.update');
        Route::delete('/admin/items/{kode}', [AdminSaranaController::class, 'destroyItem'])->name('admin.items.destroy');

        // Categories CRUD
        Route::get('/admin/categories', [AdminSaranaController::class, 'categories'])->name('admin.categories');
        Route::post('/admin/categories', [AdminSaranaController::class, 'storeCategory'])->name('admin.categories.store');
        Route::put('/admin/categories/{id}', [AdminSaranaController::class, 'updateCategory'])->name('admin.categories.update');
        Route::delete('/admin/categories/{id}', [AdminSaranaController::class, 'destroyCategory'])->name('admin.categories.destroy');

        // Verifications
        Route::get('/admin/verifications', [AdminSaranaController::class, 'verifications'])->name('admin.verifications');
        Route::post('/admin/verifications/{kode}/approve', [AdminSaranaController::class, 'approveRequest'])->name('admin.verifications.approve');
        Route::post('/admin/verifications/{kode}/reject', [AdminSaranaController::class, 'rejectRequest'])->name('admin.verifications.reject');
        Route::post('/admin/verifications/{kode}/confirm-return', [AdminSaranaController::class, 'confirmReturn'])->name('admin.verifications.confirm-return');
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

// Fallback untuk melayani file uploads jika diakses lewat /storage/uploads/
Route::get('/storage/uploads/{any}', function ($any) {
    $path = public_path('uploads/' . $any);
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
})->where('any', '.*');
