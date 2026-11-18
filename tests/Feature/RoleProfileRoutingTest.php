<?php

namespace Tests\Feature;

use App\Models\Akun;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class RoleProfileRoutingTest
 *
 * Pengujian otomatis untuk memastikan rute, otorisasi, dan tampilan halaman Profil
 * pada masing-masing peran (Siswa/Pegawai, Admin Sarana, Admin Sistem) mengarah
 * ke versi antarmuka masing-masing tanpa percampuran lintas peran (cross-role confusion).
 */
class RoleProfileRoutingTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Memastikan pengguna peran siswa dan pegawai mengakses versi profil peminjam (`user.profile`).
     *
     * @return void
     */
    public function test_borrower_roles_render_user_profile_view(): void
    {
        $siswa = Akun::where('role', 'siswa')->where('must_change_password', false)->first()
            ?? Akun::where('role', 'siswa')->first();
        $this->assertNotNull($siswa, 'Dibutuhkan akun siswa untuk pengujian.');
        if ($siswa->must_change_password) {
            $siswa->must_change_password = false;
            $siswa->save();
        }
        \Illuminate\Support\Facades\Cache::forget('active_user_session_' . $siswa->id_akun);

        $response = $this->actingAs($siswa)->get('/profile');
        $response->assertOk();
        $response->assertViewIs('user.profile');
    }

    /**
     * Memastikan Admin Sarana mengakses versi profil khusus Admin Sarana (`admin.profile`)
     * dan dialihkan secara otomatis bila mengakses URL `/profile`.
     *
     * @return void
     */
    public function test_admin_sarana_uses_dedicated_admin_sarana_profile(): void
    {
        $adminSarana = Akun::where('role', 'admin_sarana')->first();
        $this->assertNotNull($adminSarana, 'Dibutuhkan akun admin_sarana untuk pengujian.');

        // 1. Mengakses rute khusus Admin Sarana
        $response = $this->actingAs($adminSarana)->get('/admin/profile');
        $response->assertOk();
        $response->assertViewIs('admin.profile');

        // 2. Mengakses rute generik /profile otomatis dialihkan ke profil Admin Sarana
        $redirectResponse = $this->actingAs($adminSarana)->get('/profile');
        $redirectResponse->assertRedirect(route('admin.profile'));

        // 3. Dilarang mengakses profil Admin Sistem
        $forbiddenResponse = $this->actingAs($adminSarana)->get('/admin-sistem/profile');
        $forbiddenResponse->assertForbidden();
    }

    /**
     * Memastikan Admin Sistem mengakses versi profil khusus Admin Sistem (`admin.system.profile`)
     * dan dialihkan secara otomatis bila mengakses URL `/profile`.
     *
     * @return void
     */
    public function test_admin_sistem_uses_dedicated_admin_sistem_profile(): void
    {
        $adminSistem = Akun::where('role', 'admin_sistem')->first();
        $this->assertNotNull($adminSistem, 'Dibutuhkan akun admin_sistem untuk pengujian.');

        // 1. Mengakses rute khusus Admin Sistem
        $response = $this->actingAs($adminSistem)->get('/admin-sistem/profile');
        $response->assertOk();
        $response->assertViewIs('admin.system.profile');

        // 2. Mengakses rute generik /profile otomatis dialihkan ke profil Admin Sistem
        $redirectResponse = $this->actingAs($adminSistem)->get('/profile');
        $redirectResponse->assertRedirect(route('admin.sistem.profile'));

        // 3. Dilarang mengakses profil Admin Sarana
        $forbiddenResponse = $this->actingAs($adminSistem)->get('/admin/profile');
        $forbiddenResponse->assertForbidden();
    }

    /**
     * Memastikan Admin Sistem dapat memperbarui data biodata profilnya sendiri.
     *
     * @return void
     */
    public function test_admin_sistem_can_update_profile_data(): void
    {
        $adminSistem = Akun::where('role', 'admin_sistem')->first();
        $this->assertNotNull($adminSistem);

        $response = $this->actingAs($adminSistem)->put('/admin-sistem/profile/update', [
            'nama'         => 'Admin Sistem Updated',
            'nomor_kontak' => '081234567899',
            'email'        => $adminSistem->email,
        ]);

        $response->assertRedirect(route('admin.sistem.profile', ['tab' => 'profile']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('akun', [
            'id_akun'      => $adminSistem->id_akun,
            'nama'         => 'Admin Sistem Updated',
            'nomor_kontak' => '081234567899',
        ]);
    }
}
