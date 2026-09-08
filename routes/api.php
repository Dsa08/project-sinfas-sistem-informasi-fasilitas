<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — SINFAS (Sistem Informasi Fasilitas)
|--------------------------------------------------------------------------
| Rute API ini ditujukan untuk pertukaran data secara stateless (JSON),
| integrasi aplikasi mobile, atau layanan eksternal di masa mendatang.
| Rute secara default menerapkan middleware group 'api' dan proteksi Sanctum.
|--------------------------------------------------------------------------
*/

// Endpoint untuk mendapatkan data akun pengguna yang sedang terautentikasi via token Sanctum
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Data pengguna berhasil diambil.',
        'data'    => $request->user(),
    ]);
});
