<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * Service Provider RouteServiceProvider
 *
 * Mengelola pemetaan rute URL aplikasi, pengikatan model (route model binding),
 * filter pola parameter, serta pembatasan laju lalu lintas (*rate limiting*) pada sistem SINFAS.
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * Jalur URL beranda bawaan setelah pengguna berhasil terautentikasi.
     * Catatan: Untuk pengguna bertipe peran spesifik, pengalihan diatur lebih lanjut oleh helper `RoleHome::url()`.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Memulai konfigurasi perutean aplikasi dan mendefinisikan pembatasan laju request.
     *
     * Konfigurasi:
     * - RateLimiter 'api': Membatasi maksimal 60 request per menit per pengguna atau alamat IP.
     * - Rute 'api': Didaftarkan dengan middleware 'api' dan prefix '/api' (`routes/api.php`).
     * - Rute 'web': Didaftarkan dengan middleware 'web' (`routes/web.php`).
     *
     * @return void
     */
    public function boot(): void
    {
        // Batasi frekuensi pemanggilan endpoint API untuk mencegah beban berlebih (DDoS)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Daftarkan kelompok rute antarmuka web dan pertukaran data API
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
