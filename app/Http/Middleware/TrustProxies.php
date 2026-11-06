<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

/**
 * Middleware TrustProxies
 *
 * Mengonfigurasi daftar reverse proxy atau penyeimbang beban (*load balancer*) yang dipercaya sistem.
 * Memastikan aplikasi dapat mendeteksi alamat IP asli klien pengguna, protokol koneksi (HTTP/HTTPS),
 * serta host asli saat SINFAS berjalan di balik proxy web server (seperti Nginx, Cloudflare, atau Laragon).
 */
class TrustProxies extends Middleware
{
    /**
     * Alamat IP atau blok CIDR proxy yang dipercaya oleh aplikasi.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * Bitmask header HTTP yang digunakan untuk mengekstrak informasi asli klien dari proxy.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
