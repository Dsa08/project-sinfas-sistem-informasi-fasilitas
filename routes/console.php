<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Minishlink\WebPush\VAPID;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('webpush:generate-keys', function () {
    $keys = VAPID::createVapidKeys();
    $this->line('WEBPUSH_VAPID_PUBLIC_KEY=' . $keys['publicKey']);
    $this->line('WEBPUSH_VAPID_PRIVATE_KEY=' . $keys['privateKey']);
    $this->line('WEBPUSH_VAPID_SUBJECT=mailto:admin@example.com');
    $this->warn('Simpan private key dengan aman di .env dan jangan commit file .env.');
})->purpose('Generate VAPID keys for Web Push');
