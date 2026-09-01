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

    // User Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Admin Sarana Routes
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/items', function () {
        return view('admin.items');
    })->name('admin.items');

    Route::get('/admin/verifications', function () {
        return view('admin.verifications');
    })->name('admin.verifications');


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

