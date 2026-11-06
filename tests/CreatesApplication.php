<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

/**
 * Trait CreatesApplication
 *
 * Menyediakan prosedur inisialisasi dan *bootstrapping* instans aplikasi Laravel
 * yang diisolasi khusus untuk kebutuhan eksekusi *automated testing*.
 */
trait CreatesApplication
{
    /**
     * Membangun dan mengembalikan instans aplikasi yang telah siap digunakan untuk pengujian.
     *
     * @return Application Instans aplikasi Laravel yang telah dibootstrap.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
