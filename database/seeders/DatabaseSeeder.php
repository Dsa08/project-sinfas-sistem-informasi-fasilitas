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

        // Akun Siswa tambahan
        Akun::updateOrCreate(
            ['username' => 'siti'],
            [
                'nis' => '10223002',
                'nip' => null,
                'nama' => 'Siti Nurhaliza',
                'nomor_kontak' => '081234567891',
                'role' => 'siswa',
                'password' => Hash::make('password123'),
            ]
        );

        Akun::updateOrCreate(
            ['username' => 'budi'],
            [
                'nis' => '10223003',
                'nip' => null,
                'nama' => 'Budi Santoso',
                'nomor_kontak' => '081234567892',
                'role' => 'siswa',
                'password' => Hash::make('password123'),
            ]
        );

        Akun::updateOrCreate(
            ['username' => 'dewi'],
            [
                'nis' => '10223004',
                'nip' => null,
                'nama' => 'Dewi Lestari',
                'nomor_kontak' => '081234567893',
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

        // ================================================
        // 4. Data Dummy Kategori & Barang (5 Kategori, masing-masing 5 Barang)
        // ================================================
        $this->call(BarangKategoriSeeder::class);

        // ================================================
        // 5. Data Dummy Peminjaman (beberapa menunggu, beberapa disetujui)
        // ================================================
        $peminjamanData = [
            // Menunggu verifikasi
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
            // Disetujui (belum dikembalikan → "Currently Borrowed")
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
            // Disetujui + sudah ada pengembalian (pending confirm return)
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

        // ================================================
        // 7. Data Dummy Pengembalian (untuk pending returns)
        // ================================================
        $pengembalianData = [
            [
                'kode_kembali'     => 'KMB-2024-001',
                'kode_pinjam'      => 'PNJ-2024-008',
                'tanggal_kembali'  => '2024-03-18',
                'kondisi_barang'   => null, // belum dikonfirmasi admin
                'bukti_foto_video' => null,
            ],
            [
                'kode_kembali'     => 'KMB-2024-002',
                'kode_pinjam'      => 'PNJ-2024-009',
                'tanggal_kembali'  => '2024-03-19',
                'kondisi_barang'   => null,
                'bukti_foto_video' => null,
            ],
        ];

        foreach ($pengembalianData as $item) {
            Pengembalian::updateOrCreate(['kode_kembali' => $item['kode_kembali']], $item);
        }
    }
}
