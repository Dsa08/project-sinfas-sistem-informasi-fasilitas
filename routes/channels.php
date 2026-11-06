<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels — SINFAS
|--------------------------------------------------------------------------
| Berkas ini mendaftarkan seluruh saluran siaran (*broadcast channels*) acara real-time.
| Mengatur callback otorisasi untuk memastikan hanya pengguna terautentikasi yang berhak
| mendengarkan aliran data pada saluran privatnya masing-masing.
|--------------------------------------------------------------------------
*/

// Otorisasi saluran privat pengguna: hanya pemilik akun yang berhak mendengarkan event miliknya
Broadcast::channel('App.Models.Akun.{id}', function ($user, $id) {
    return (int) $user->id_akun === (int) $id;
});

// Kompatibilitas saluran bawaan model User
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) ($user->id ?? $user->id_akun) === (int) $id;
});
