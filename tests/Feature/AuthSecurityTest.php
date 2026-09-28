<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\RateLimiter;

class AuthSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('testuser|127.0.0.1');
    }

    public function test_registration_routes_are_not_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_throttling_triggers_after_multiple_failed_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'unknown_user_test',
                'password' => 'wrong_password',
            ]);
        }

        // Percobaan ke-6 harus diblokir oleh RateLimiter
        $response = $this->post('/login', [
            'email' => 'unknown_user_test',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('email');
        $errors = session('errors')->get('email');
        $this->assertStringContainsString('Terlalu banyak percobaan login', $errors[0]);
    }


}
