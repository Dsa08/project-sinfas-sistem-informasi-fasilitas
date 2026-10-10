<?php

namespace Tests\Unit;

use App\Models\Akun;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AkunRoleTest extends TestCase
{
    #[DataProvider('borrowerRoles')]
    public function test_only_student_and_employee_accounts_can_borrow(string $role, bool $expected): void
    {
        $akun = new Akun(['role' => $role]);

        $this->assertSame($expected, $akun->isPeminjam());
    }

    public function test_temporary_password_flag_is_cast_to_boolean(): void
    {
        $account = new Akun(['must_change_password' => 1]);

        $this->assertTrue($account->must_change_password);
    }

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
