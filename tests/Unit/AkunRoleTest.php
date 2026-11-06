<?php

namespace Tests\Unit;

use App\Models\Akun;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Class AkunRoleTest
 *
 * Menguji unit logika bisnis model Akun terkait hak akses peminjaman sarana
 * dan konversi tipe data atribut model (Eloquent attribute casting).
 */
class AkunRoleTest extends TestCase
{
    /**
     * Memastikan hanya akun dengan peran 'siswa' dan 'pegawai' yang berhak mengajukan peminjaman sarana.
     * Menggunakan Data Provider untuk menguji kombinasi seluruh peran pengguna (siswa, pegawai, admin_sarana, admin_sistem).
     *
     * @param string $role Nilai string peran akun pengguna.
     * @param bool $expected Ekspektasi nilai kembalian dari metode isPeminjam().
     * @return void
     */
    #[DataProvider('borrowerRoles')]
    public function test_only_student_and_employee_accounts_can_borrow(string $role, bool $expected): void
    {
        $akun = new Akun(['role' => $role]);

        $this->assertSame($expected, $akun->isPeminjam());
    }

    /**
     * Memastikan atribut `must_change_password` dikonversi menjadi tipe data boolean secara akurat oleh Eloquent cast.
     *
     * @return void
     */
    public function test_temporary_password_flag_is_cast_to_boolean(): void
    {
        $account = new Akun(['must_change_password' => 1]);

        $this->assertTrue($account->must_change_password);
    }

    /**
     * Menyediakan dataset matriks peran pengguna untuk pengujian hak akses peminjaman sarana.
     *
     * @return array<string, array{0: string, 1: bool}> Kumpulan data pengujian peran dan ekspektasi hak pinjam.
     */
    public static function borrowerRoles(): array
    {
        return [
            'student' => ['siswa', true],
            'employee' => ['pegawai', true],
            'facilities admin' => ['admin_sarana', false],
            'system admin' => ['admin_sistem', false],
        ];
    }
}
