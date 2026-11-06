<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class ExampleTest
 *
 * Menguji ketersediaan halaman publik dan kesiapan antarmuka awal aplikasi SINFAS.
 */
class ExampleTest extends TestCase
{
    /**
     * Memastikan halaman landing publik ('/') dapat diakses dengan sukses (HTTP 200),
     * merender template view 'landing', serta memuat teks panduan operasional peminjaman sarana.
     *
     * @return void
     */
    public function test_landing_page_shows_peminjaman_guide(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('landing');
        $response->assertSee('3 Langkah Mudah Peminjaman Sarana');
    }

    /**
     * Memastikan halaman formulir login ('/login') dapat dirender tanpa kendala (HTTP 200 OK).
     *
     * @return void
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }
}
