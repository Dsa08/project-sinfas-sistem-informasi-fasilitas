<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Siswa;
use App\Models\Pegawai;
use App\Models\Akun;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Data Dummy Siswa
        $siswaData = [
            [
                'nis' => '10223001',
                'nama' => 'Ahmad Fadli',
                'email' => 'ahmad.fadli@gmail.com',
                'no_hp' => '081234567890',
            ],
            [
                'nis' => '10223002',
                'nama' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@gmail.com',
                'no_hp' => '081234567891',
            ],
            [
                'nis' => '10223003',
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@gmail.com',
                'no_hp' => '081234567892',
            ],
            [
                'nis' => '10223004',
                'nama' => 'Dewi Lestari',
                'email' => 'dewi.lestari@gmail.com',
                'no_hp' => '081234567893',
            ],
        ];

        foreach ($siswaData as $item) {
            Siswa::updateOrCreate(['nis' => $item['nis']], $item);
        }

        // 2. Data Dummy Pegawai
        $pegawaiData = [
            [
                'nip' => '198501012010011001',
                'nama' => 'Hendra Setiawan',
            ],
            [
                'nip' => '198802022012022002',
                'nama' => 'Rina Wijaya',
            ],
            [
                'nip' => '199003032015031003',
                'nama' => 'Bambang Pamungkas',
            ],
        ];

        foreach ($pegawaiData as $item) {
            Pegawai::updateOrCreate(['nip' => $item['nip']], $item);
        }

        // 3. Akun Siap Pakai untuk Testing Cepat
        // Akun Siswa (Bisa login dengan NIS 10223001 atau username 'ahmad', password: 'password123')
        Akun::updateOrCreate(
            ['username' => 'ahmad'],
            [
                'nis' => '10223001',
                'nip' => null,
                'nama' => 'Ahmad Fadli',
                'nomor_kontak' => '081234567890',
                'role' => 'siswa',
                'password' => Hash::make('password123'),
            ]
        );

        // Akun Admin Sarana (Bisa login dengan NIP 198501012010011001 atau username 'admin', password: 'password123')
        Akun::updateOrCreate(
            ['username' => 'admin'],
            [
                'nis' => null,
                'nip' => '198501012010011001',
                'nama' => 'Hendra Setiawan',
                'nomor_kontak' => '081299887766',
                'role' => 'admin_sarana',
                'password' => Hash::make('password123'),
            ]
        );

        // Akun Khusus Admin Sistem (Bisa login dengan username 'adminsistem' atau 'admin_sistem', password: 'password123')
        Akun::updateOrCreate(
            ['username' => 'adminsistem'],
            [
                'nis' => null,
                'nip' => null,
                'nama' => 'Admin Sistem',
                'nomor_kontak' => '081299001122',
                'role' => 'admin_sistem',
                'password' => Hash::make('password123'),
            ]
        );
    }
}

