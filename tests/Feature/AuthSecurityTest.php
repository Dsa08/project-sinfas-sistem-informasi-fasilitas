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

    public function test_password_reset_never_discloses_a_recovery_link_for_unknown_identifier(): void
    {
        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'unknown-reset-test-user',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('status');
        $response->assertSessionMissing('direct_reset_url');
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

        // Verifikasi bahwa percobaan login tercatat di tabel audit login_logs
        $this->assertDatabaseHas('login_logs', [
            'identifier' => 'unknown_user_test',
            'status' => 'terkunci_rate_limit',
        ]);
    }

    public function test_failed_login_creates_audit_log_entry(): void
    {
        $this->post('/login', [
            'email' => 'audit_test_unknown_user',
            'password' => 'secret123',
        ]);

        $this->assertDatabaseHas('login_logs', [
            'identifier' => 'audit_test_unknown_user',
            'status' => 'gagal_akun_tidak_ditemukan',
        ]);
    }

    public function test_user_login_records_current_session_id(): void
    {
        $user = \App\Models\Akun::where('username', 'ahmad')->first();
        if (! $user) {
            $user = \App\Models\Akun::create([
                'username' => 'test_single_session_user',
                'nama' => 'Single Session Tester',
                'email' => 'single_session@smkn11bdg.sch.id',
                'role' => 'siswa',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'is_active' => true,
            ]);
        }

        $response = $this->post('/login', [
            'email' => $user->username,
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertNotNull($user->current_session_id);
    }

    public function test_superseded_session_is_automatically_logged_out_with_warning(): void
    {
        $user = \App\Models\Akun::where('username', 'ahmad')->first();
        if (! $user) {
            $user = \App\Models\Akun::create([
                'username' => 'test_superseded_user',
                'nama' => 'Superseded Tester',
                'email' => 'superseded@smkn11bdg.sch.id',
                'role' => 'siswa',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'is_active' => true,
            ]);
        }

        // Simulasikan perangkat baru login sehingga current_session_id di database terisi ID sesi lain
        $user->forceFill(['current_session_id' => 'different-session-from-device-b'])->save();

        // Kirim request dari peramban lama (yang memiliki session ID lokal berbeda)
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
        $response->assertSessionHas('warning');
        $this->assertGuest();
    }

    public function test_admin_with_2fa_is_redirected_to_two_factor_challenge(): void
    {
        $admin = \App\Models\Akun::where('username', 'admin_2fa_test')->first();
        if (! $admin) {
            $admin = \App\Models\Akun::create([
                'username' => 'admin_2fa_test',
                'nama' => 'Admin 2FA Tester',
                'email' => 'admin_2fa_test@smkn11bdg.sch.id',
                'role' => 'admin_sarana',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'is_active' => true,
            ]);
        }

        $secret = \App\Services\TotpService::generateSecret();
        $admin->two_factor_secret = $secret;
        $admin->two_factor_recovery_codes = ['ABCD-1234', 'WXYZ-5678'];
        $admin->two_factor_confirmed_at = now();
        $admin->save();

        $response = $this->post('/login', [
            'email' => 'admin_2fa_test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login/two-factor');
        $this->assertGuest();
        $this->assertEquals($admin->id_akun, session('login.2fa.id'));
    }

    public function test_admin_2fa_challenge_succeeds_with_valid_totp(): void
    {
        $admin = \App\Models\Akun::where('username', 'admin_2fa_test')->first();
        $secret = $admin->two_factor_secret;
        $validOtp = \App\Services\TotpService::calculateOtp($secret);

        $response = $this->withSession(['login.2fa.id' => $admin->id_akun])
            ->post('/login/two-factor', [
                'code' => $validOtp,
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_2fa_challenge_fails_with_invalid_totp(): void
    {
        $admin = \App\Models\Akun::where('username', 'admin_2fa_test')->first();

        $response = $this->withSession(['login.2fa.id' => $admin->id_akun])
            ->from('/login/two-factor')
            ->post('/login/two-factor', [
                'code' => '999999',
            ]);

        $response->assertRedirect('/login/two-factor');
        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_admin_2fa_challenge_succeeds_with_recovery_code(): void
    {
        $admin = \App\Models\Akun::where('username', 'admin_2fa_test')->first();
        $codes = $admin->two_factor_recovery_codes;
        $usedCode = $codes[0];

        $response = $this->withSession(['login.2fa.id' => $admin->id_akun])
            ->post('/login/two-factor', [
                'recovery_code' => $usedCode,
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        // Verifikasi bahwa recovery code yang dipakai telah hangus/dikonsumsi
        $admin->refresh();
        $this->assertNotContains($usedCode, $admin->two_factor_recovery_codes);
    }
}

