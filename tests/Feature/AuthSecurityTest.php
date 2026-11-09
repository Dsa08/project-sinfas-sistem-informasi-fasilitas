<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Services\LoginAuditService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Class AuthSecurityTest
 *
 * Menguji seluruh mekanisme keamanan autentikasi pada aplikasi SINFAS:
 * 1. Pencegahan rute pendaftaran mandiri publik (Closed Registration).
 * 2. Perlindungan rute terautentikasi terhadap akses pengguna tamu (Guest).
 * 3. Keamanan reset sandi tanpa membocorkan tautan langsung pemulihan ke pengguna luar.
 * 4. Pembatasan laju percobaan login (Rate Limiting) dari serangan brute-force.
 * 5. Penguncian sesi tunggal (Single Session Lock) via cache server.
 * 6. Pencatatan log audit login berbasis berkas (Login Audit Log) tanpa tabel database.
 * 7. Penerbitan kata sandi sementara terstruktur (format SMK11-XXXX).
 */
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
    }

    /**
     * Memastikan middleware CheckSingleSession memutus sesi browser yang kedaluwarsa
     * ketika akun yang bersangkutan baru saja masuk dari peramban/perangkat lain.
     *
     * @return void
     */
    public function test_single_session_lock_middleware_blocks_stale_session(): void
    {
        $user = Akun::where('role', 'siswa')->first() ?? Akun::first();
        $this->assertNotNull($user, 'Dibutuhkan setidaknya satu akun untuk pengujian single session.');

        $this->actingAs($user);

        // Simulasikan perangkat lain login dan menimpa kunci cache sesi pengguna
        Cache::put('active_user_session_' . $user->id_akun, 'other_device_session_token_' . uniqid());

        $response = $this->get('/dashboard');

        // Sesi pada peramban ini harus diputus dan diarahkan ke login dengan notifikasi warning
        $response->assertRedirect('/login');
        $response->assertSessionHas('warning');
        $this->assertGuest();
    }

    /**
     * Memastikan endpoint heartbeat session-check mengembalikan status 200 saat sesi valid,
     * dan otomatis mengembalikan status 401 JSON serta memutus sesi saat terdeteksi login di perangkat lain.
     *
     * @return void
     */
    public function test_single_session_heartbeat_detects_concurrent_login_instantly(): void
    {
        $user = Akun::where('role', 'siswa')->first() ?? Akun::first();
        $this->assertNotNull($user);

        // 1. Kasus Sesi Valid: Tidak ada konflik perangkat lain
        Cache::forget('active_user_session_' . $user->id_akun);

        $validResponse = $this->actingAs($user)->getJson('/auth/session-check');
        $validResponse->assertOk();
        $validResponse->assertJson(['valid' => true]);

        // 2. Kasus Perangkat Lain Masuk: Kunci cache ditimpa oleh perangkat lain
        Cache::put('active_user_session_' . $user->id_akun, 'hp_session_token_' . uniqid());

        $staleResponse = $this->actingAs($user)->getJson('/auth/session-check');
        $staleResponse->assertStatus(401);
        $staleResponse->assertJson([
            'message' => 'Sesi Anda telah berakhir karena akun ini baru saja login di perangkat atau peramban lain demi keamanan.',
            'redirect' => route('login'),
        ]);
        $this->assertGuest();

        // 3. Halaman login menampilkan peringatan terminasi sesi perangkat
        $loginPage = $this->get('/login?session_terminated=1');
        $loginPage->assertOk();
        $loginPage->assertSee('Akun Anda telah masuk di perangkat lain');
    }

    /**
     * Memastikan layanan LoginAuditService mencatat aktivitas ke berkas log audit tanpa memerlukan tabel database.
     *
     * @return void
     */
    public function test_login_audit_service_records_events_properly(): void
    {
        $uniqueIdentifier = 'audit_test_user_' . uniqid();

        // Lakukan request login yang gagal untuk memicu pencatatan audit log
        $this->post('/login', [
            'email' => $uniqueIdentifier,
            'password' => 'wrong_password',
        ]);

        $recentLogs = LoginAuditService::getRecentLogs(10);
        $this->assertNotEmpty($recentLogs);

        // Verifikasi entri pencatatan sesuai dengan identifier unik
        $matched = collect($recentLogs)->first(fn ($item) => ($item['identifier'] ?? '') === $uniqueIdentifier);
        $this->assertNotNull($matched);
        $this->assertSame('gagal_akun_tidak_ditemukan', $matched['status']);
    }

    /**
     * Memastikan fitur reset kata sandi oleh Admin menghasilkan format sementara SMK11-XXXX
     * dan menyetel flag must_change_password menjadi true.
     *
     * @return void
     */
    public function test_admin_can_reset_password_with_structured_temporary_format(): void
    {
        $admin = Akun::where('role', 'admin_sistem')->first();
        if (! $admin) {
            $admin = Akun::create([
                'nama' => 'Admin Sistem Uji',
                'username' => 'adminsistem_test_' . uniqid(),
                'role' => 'admin_sistem',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]);
        }

        $targetAccount = Akun::where('role', 'siswa')->first();
        $this->assertNotNull($targetAccount, 'Dibutuhkan satu akun siswa untuk pengujian reset password.');

        $this->actingAs($admin);

        $response = $this->post(route('admin.sistem.accounts.reset', $targetAccount->id_akun));
        $response->assertRedirect(route('admin.sistem.accounts'));
        $response->assertSessionHas('temporary_password');

        $temporaryPassword = session('temporary_password');
        // Format harus berupa SMK11- diikuti 4 angka
        $this->assertMatchesRegularExpression('/^SMK11-\d{4}$/', $temporaryPassword);

        $targetAccount->refresh();
        $this->assertTrue($targetAccount->must_change_password);
    }

    /**
     * Memastikan pembuatan akun massal otomatis untuk siswa yang belum berakun
     * berhasil menghubungkan akun baru dengan profil siswa dan menetapkan flag must_change_password.
     *
     * @return void
     */
    public function test_batch_generate_student_accounts_provisions_unregistered_students(): void
    {
        $admin = Akun::where('role', 'admin_sistem')->first();
        if (! $admin) {
            $admin = Akun::create([
                'nama' => 'Admin Sistem Uji',
                'username' => 'adminsistem_test_' . uniqid(),
                'role' => 'admin_sistem',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]);
        }

        // Siapkan record siswa uji coba tanpa akun
        $testNis = 'TEST' . random_int(10000, 99999);
        $siswa = \App\Models\Siswa::create([
            'nis'     => $testNis,
            'nama'    => 'Siswa Massal Uji Coba',
            'kelas'   => 'XII RPL 1',
            'no_hp'   => '081234567890',
            'email'   => 'siswa.' . $testNis . '@sekolah.sch.id',
            'id_akun' => null,
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('admin.sistem.accounts.batch-students'));
        $response->assertRedirect(route('admin.sistem.accounts'));
        $response->assertSessionHas('success');

        // Pastikan relasi id_akun pada siswa telah terisi
        $siswa->refresh();
        $this->assertNotNull($siswa->id_akun);

        $newAccount = Akun::find($siswa->id_akun);
        $this->assertNotNull($newAccount);
        $this->assertSame($testNis, $newAccount->username);
        $this->assertSame('siswa', $newAccount->role);
        $this->assertTrue($newAccount->must_change_password);

        // Bersihkan data uji coba agar tidak mengotori basis data
        $siswa->delete();
        $newAccount->delete();
    }
}
