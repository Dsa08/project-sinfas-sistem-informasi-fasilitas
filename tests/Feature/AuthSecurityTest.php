<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Akun;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('testuser|127.0.0.1');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
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

    public function test_registration_rejects_weak_password(): void
    {
        $response = $this->post('/register', [
            'full_name' => 'John Doe',
            'nis_nip' => '12345',
            'email' => 'johndoe@test.com',
            'username' => 'johndoe',
            'contact_number' => '08123456789',
            'password' => '12345', // kurang dari 8 karakter
            'password_confirmation' => '12345',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
