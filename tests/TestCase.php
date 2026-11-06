<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Abstract Class TestCase
 *
 * Kelas induk (*base test case*) untuk seluruh pengujian otomatis pada aplikasi SINFAS.
 * Menginisialisasi kernel framework dalam lingkungan pengujian (`APP_ENV=testing`).
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
