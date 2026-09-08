<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use App\Models\Pegawai;
use App\Models\Akun;
use App\Models\Kategori;
use App\Models\Barang;
use App\Models\Peminjaman;
use App\Models\Pengembalian;

/**
 * Seeder DatabaseSeeder
 * 
 * Seeder utama untuk menginisialisasi lingkungan pengujian dan basis data awal aplikasi SINFAS:
 * 1. Data Master Siswa (NIS, Nama, Email, Kontak).
 * 2. Data Master Pegawai / Staf Guru (NIP, Nama).
 * 3. Akun Login Bawaan Siap Pakai:
 *    - Siswa: 'ahmad', 'siti', 'budi', 'dewi' (Password: 'password123')
 *    - Admin Sarana: 'admin' (Password: 'password123')
 *    - Admin Sistem: 'adminsistem' (Password: 'password123')
 * 4. Pemanggilan Seeder Kategori & Barang (BarangKategoriSeeder).
 * 5. Data Transaksi Peminjaman Awal (Menunggu, Disetujui, dan Pengembalian).
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Menjalankan seluruh proses seeding database secara berurutan.
     *
     * @return void
     */
    public function run(): void
    {
        // =====================================================================
        // 1. Inisialisasi Data Master Siswa
        // =====================================================================
        $siswaData = [
            [
                'nis'   => '10223001',
                'nama'  => 'Ahmad Fadli',
                'email' => 'ahmad.fadli@gmail.com',
                'no_hp' => '081234567890',
            ],
            [
                'nis'   => '10223002',
                'nama'  => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@gmail.com',
                'no_hp' => '081234567891',
            ],
            [
                'nis'   => '10223003',
                'nama'  => 'Budi Santoso',
                'email' => 'budi.santoso@gmail.com',
                'no_hp' => '081234567892',
            ],
            [
                'nis'   => '10223004',
                'nama'  => 'Dewi Lestari',
                'email' => 'dewi.lestari@gmail.com',
                'no_hp' => '081234567893',
            ],
        ];

        foreach ($siswaData as $item) {
            Siswa::updateOrCreate(['nis' => $item['nis']], $item);
        }

        // =====================================================================
        // 2. Inisialisasi Data Master Pegawai (Guru / Staf Sarpras)
        // =====================================================================
        $pegawaiData = [
            [
                'nip'  => '198501012010011001',
                'nama' => 'Hendra Setiawan',
            ],
            [
                'nip'  => '198802022012022002',
                'nama' => 'Rina Wijaya',
            ],
            [
                'nip'  => '199003032015031003',
                'nama' => 'Bambang Pamungkas',
            ],
        ];

        foreach ($pegawaiData as $item) {
            Pegawai::updateOrCreate(['nip' => $item['nip']], $item);
        }

        // =====================================================================
        // 3. Akun Bawaan Siap Pakai (Testing & Evaluasi Pengembang)
        // =====================================================================
        
        // Akun Siswa 1: Ahmad Fadli (Login via username: 'ahmad' atau NIS: '10223001', pass: 'password123')
        Akun::updateOrCreate(
            ['username' => 'ahmad'],
            [
                'nis'          => '10223001',
                'nip'          => null,
                'nama'         => 'Ahmad Fadli',
                'email'        => 'ahmad.fadli@gmail.com',
                'nomor_kontak' => '081234567890',
                'role'         => 'siswa',
                'password'     => Hash::make('password123'),
                'is_active'    => true,
            ]
        );

        // Akun Siswa 2: Siti Nurhaliza
        Akun::updateOrCreate(
            ['username' => 'siti'],
            [
                'nis'          => '10223002',
                'nip'          => null,
                'nama'         => 'Siti Nurhaliza',
                'email'        => 'siti.nurhaliza@gmail.com',
                'nomor_kontak' => '081234567891',
                'role'         => 'siswa',
                'password'     => Hash::make('password123'),
                'is_active'    => true,
            ]
        );

        // Akun Siswa 3: Budi Santoso
        Akun::updateOrCreate(
            ['username' => 'budi'],
            [
                'nis'          => '10223003',
                'nip'          => null,
                'nama'         => 'Budi Santoso',
                'email'        => 'budi.santoso@gmail.com',
                'nomor_kontak' => '081234567892',
                'role'         => 'siswa',
                'password'     => Hash::make('password123'),
                'is_active'    => true,
            ]
        );

        // Akun Siswa 4: Dewi Lestari
        Akun::updateOrCreate(
            ['username' => 'dewi'],
            [
                'nis'          => '10223004',
                'nip'          => null,
                'nama'         => 'Dewi Lestari',
                'email'        => 'dewi.lestari@gmail.com',
                'nomor_kontak' => '081234567893',
                'role'         => 'siswa',
                'password'     => Hash::make('password123'),
                'is_active'    => true,
            ]
        );

        // Akun Admin Sarana: Hendra Setiawan (Login via username: 'admin' atau NIP: '198501012010011001', pass: 'password123')
        Akun::updateOrCreate(
            ['username' => 'admin'],
            [
                'nis'          => null,
                'nip'          => '198501012010011001',
                'nama'         => 'Hendra Setiawan',
                'email'        => 'hendra.sarana@sinfas.sch.id',
                'nomor_kontak' => '081299887766',
                'role'         => 'admin_sarana',
                'password'     => Hash::make('password123'),
                'is_active'    => true,
            ]
        );

        // Akun Admin Sistem: Administrator IT (Login via username: 'adminsistem', pass: 'password123')
        Akun::updateOrCreate(
            ['username' => 'adminsistem'],
            [
                'nis'          => null,
                'nip'          => null,
                'nama'         => 'Administrator Sistem',
                'email'        => 'admin.sistem@sinfas.sch.id',
                'nomor_kontak' => '081299001122',
                'role'         => 'admin_sistem',
                'password'     => Hash::make('password123'),
                'is_active'    => true,
            ]
        );

        // =====================================================================
        // 4. Inisialisasi Master Kategori & Inventaris Barang
        // =====================================================================
        $this->call(BarangKategoriSeeder::class);

        // =====================================================================
        // 5. Inisialisasi Riwayat Transaksi Peminjaman Dummy
        // =====================================================================
        $peminjamanData = [
            // Status: Menunggu Verifikasi Admin
            [
                'kode_pinjam'           => 'PNJ-2024-001',
                'nis'                   => '10223001',
                'kode_barang'           => 'PRJ-001',
                'tanggal_pinjam'        => '2024-03-15',
                'keterangan_penggunaan' => 'Presentasi tugas akhir kelas XII',
                'lokasi_penggunaan'     => 'Ruang 31',
                'status_pengajuan'      => 'menunggu',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-002',
                'nis'                   => '10223002',
                'kode_barang'           => 'AUD-002',
                'tanggal_pinjam'        => '2024-03-16',
                'keterangan_penggunaan' => 'Acara pentas seni sekolah',
                'lokasi_penggunaan'     => 'Aula Utama',
                'status_pengajuan'      => 'menunggu',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-003',
                'nis'                   => '10223003',
                'kode_barang'           => 'AUD-001',
                'tanggal_pinjam'        => '2024-03-16',
                'keterangan_penggunaan' => 'Rekaman podcast sekolah',
                'lokasi_penggunaan'     => 'Ruang 7',
                'status_pengajuan'      => 'menunggu',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-004',
                'nis'                   => '10223004',
                'kode_barang'           => 'CAM-001',
                'tanggal_pinjam'        => '2024-03-17',
                'keterangan_penggunaan' => 'Dokumentasi kegiatan OSIS',
                'lokasi_penggunaan'     => 'Lapangan Sekolah',
                'status_pengajuan'      => 'menunggu',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-005',
                'nis'                   => '10223001',
                'kode_barang'           => 'PRJ-004',
                'tanggal_pinjam'        => '2024-03-17',
                'keterangan_penggunaan' => 'Latihan presentasi lomba',
                'lokasi_penggunaan'     => 'Ruang Lab',
                'status_pengajuan'      => 'menunggu',
            ],
            // Status: Disetujui (Sedang Digunakan / Currently Borrowed)
            [
                'kode_pinjam'           => 'PNJ-2024-006',
                'nis'                   => '10223002',
                'kode_barang'           => 'KBL-001',
                'tanggal_pinjam'        => '2024-03-10',
                'keterangan_penggunaan' => 'Setup multimedia kelas',
                'lokasi_penggunaan'     => 'Ruang 12',
                'status_pengajuan'      => 'disetujui',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-007',
                'nis'                   => '10223003',
                'kode_barang'           => 'CAM-003',
                'tanggal_pinjam'        => '2024-03-12',
                'keterangan_penggunaan' => 'Foto produk untuk tugas prakarya',
                'lokasi_penggunaan'     => 'Studio Mini',
                'status_pengajuan'      => 'disetujui',
            ],
            // Status: Disetujui dan Pengembalian Diajukan (Menunggu Konfirmasi Admin)
            [
                'kode_pinjam'           => 'PNJ-2024-008',
                'nis'                   => '10223001',
                'kode_barang'           => 'PRJ-001',
                'tanggal_pinjam'        => '2024-03-08',
                'keterangan_penggunaan' => 'Presentasi proyek sains',
                'lokasi_penggunaan'     => 'Ruang 5',
                'status_pengajuan'      => 'disetujui',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-009',
                'nis'                   => '10223004',
                'kode_barang'           => 'AUD-002',
                'tanggal_pinjam'        => '2024-03-09',
                'keterangan_penggunaan' => 'Acara class meeting',
                'lokasi_penggunaan'     => 'Lapangan',
                'status_pengajuan'      => 'disetujui',
            ],
        ];

        foreach ($peminjamanData as $item) {
            Peminjaman::updateOrCreate(['kode_pinjam' => $item['kode_pinjam']], $item);
        }

        // =====================================================================
        // 6. Inisialisasi Transaksi Pengembalian Dummy
        // =====================================================================
        $pengembalianData = [
            [
                'kode_kembali'     => 'KMB-2024-001',
                'kode_pinjam'      => 'PNJ-2024-008',
                'tanggal_kembali'  => '2024-03-18',
                'kondisi_barang'   => null, // Belum dikonfirmasi admin (pending return tab)
                'bukti_foto_video' => null,
            ],
            [
                'kode_kembali'     => 'KMB-2024-002',
                'kode_pinjam'      => 'PNJ-2024-009',
                'tanggal_kembali'  => '2024-03-19',
                'kondisi_barang'   => null, // Belum dikonfirmasi admin (pending return tab)
                'bukti_foto_video' => null,
            ],
        ];

        foreach ($pengembalianData as $item) {
            Pengembalian::updateOrCreate(['kode_kembali' => $item['kode_kembali']], $item);
        }
    }
}
