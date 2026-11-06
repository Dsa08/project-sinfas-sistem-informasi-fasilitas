<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Minishlink\WebPush\VAPID;

/*
|--------------------------------------------------------------------------
| Console Routes — SINFAS
|--------------------------------------------------------------------------
| Berkas ini mendefinisikan rute perintah CLI Artisan berbasis Closure.
| Digunakan untuk kebutuhan utilitas sistem, pengujian konsol, dan pembuatan kunci VAPID.
|--------------------------------------------------------------------------
*/

// Menampilkan kutipan motivasi kerja sistem
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Menampilkan kutipan motivasi kerja sistem');

// Menghasilkan sepasang kunci kriptografi VAPID (Public & Private Key) untuk fitur Web Push Notifications
Artisan::command('webpush:generate-keys', function () {
    $keys = VAPID::createVapidKeys();
    $this->line('WEBPUSH_VAPID_PUBLIC_KEY=' . $keys['publicKey']);
    $this->line('WEBPUSH_VAPID_PRIVATE_KEY=' . $keys['privateKey']);
    $this->line('WEBPUSH_VAPID_SUBJECT=mailto:admin@example.com');
    $this->warn('Simpan private key dengan aman di berkas .env dan jangan commit ke repositori publik.');
})->purpose('Menghasilkan sepasang kunci VAPID untuk integrasi Web Push Notifications');
