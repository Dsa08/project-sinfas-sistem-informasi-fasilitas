<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Controller Induk (Base Controller) Aplikasi SINFAS.
 *
 * Menyediakan fungsionalitas dasar bagi seluruh controller seperti
 * otorisasi perizinan akses (`AuthorizesRequests`) dan validasi data masukan (`ValidatesRequests`).
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}

