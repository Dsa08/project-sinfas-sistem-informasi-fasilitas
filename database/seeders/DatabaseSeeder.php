<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Pegawai;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Penyetujuan;
use App\Models\Siswa;
use App\Models\StaffSarana;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder DatabaseSeeder (Diselaraskan dengan ERD 2)
 * 
 * Seeder utama untuk menginisialisasi lingkungan pengujian dan basis data awal aplikasi SINFAS:
 * 1. Akun Login Bawaan Siap Pakai:
 *    - Siswa: 'ahmad', 'siti', 'budi', 'dewi' (Password: 'password123')
 *    - Pegawai: 'bambang' (Password: 'password123')
 *    - Admin Sarana: 'admin' (Password: 'password123')
 *    - Admin Sistem: 'adminsistem' (Password: 'password123')
 * 2. Data Master Siswa (NIS, Nama, Kelas, Email, Kontak, terhubung ke id_akun).
 * 3. Data Master Pegawai (NIP, Nama, Jabatan, Email, Kontak, terhubung ke id_akun).
 * 4. Data Master Petugas Sarana (StaffSarana) terhubung ke akun Admin Sarana.
 * 5. Pemanggilan Seeder Kategori & Barang (BarangKategoriSeeder).
 * 6. Data Transaksi Peminjaman (Menunggu, Disetujui, dan Riwayat Penyetujuan ERD 2).
 * 7. Data Transaksi Pengembalian Dummy.
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
        // 1. Akun Bawaan Siap Pakai (Testing & Evaluasi Pengembang)
        // =====================================================================
        
        // Akun Siswa 1: Ahmad Fadli (Login via username: 'ahmad' atau NIS: '10223001', pass: 'password123')
        $akunAhmad = $this->seedAkun([
            'username'     => 'ahmad',
            'nis'          => '10223001',
            'nip'          => null,
            'nama'         => 'Ahmad Fadli',
            'email'        => 'ahmad.fadli@gmail.com',
            'nomor_kontak' => '081234567890',
            'role'         => 'siswa',
            'password'     => Hash::make('password123'),
            'is_active'    => true,
        ]);

        // Akun Siswa 2: Siti Nurhaliza
        $akunSiti = $this->seedAkun([
            'username'     => 'siti',
            'nis'          => '10223002',
            'nip'          => null,
            'nama'         => 'Siti Nurhaliza',
            'email'        => 'siti.nurhaliza@gmail.com',
            'nomor_kontak' => '081234567891',
            'role'         => 'siswa',
            'password'     => Hash::make('password123'),
            'is_active'    => true,
        ]);

        // Akun Siswa 3: Budi Santoso
        $akunBudi = $this->seedAkun([
            'username'     => 'budi',
            'nis'          => '10223003',
            'nip'          => null,
            'nama'         => 'Budi Santoso',
            'email'        => 'budi.santoso@gmail.com',
            'nomor_kontak' => '081234567892',
            'role'         => 'siswa',
            'password'     => Hash::make('password123'),
            'is_active'    => true,
        ]);

        // Akun Siswa 4: Dewi Lestari
        $akunDewi = $this->seedAkun([
            'username'     => 'dewi',
            'nis'          => '10223004',
            'nip'          => null,
            'nama'         => 'Dewi Lestari',
            'email'        => 'dewi.lestari@gmail.com',
            'nomor_kontak' => '081234567893',
            'role'         => 'siswa',
            'password'     => Hash::make('password123'),
            'is_active'    => true,
        ]);

        // Akun Pegawai (Guru): Bambang Pamungkas (Login via username: 'bambang' atau NIP: '199003032015031003', pass: 'password123')
        $akunBambang = $this->seedAkun([
            'username'     => 'bambang',
            'nis'          => null,
            'nip'          => '199003032015031003',
            'nama'         => 'Bambang Pamungkas',
            'email'        => 'bambang.guru@sinfas.sch.id',
            'nomor_kontak' => '081277665544',
            'role'         => 'pegawai',
            'password'     => Hash::make('password123'),
            'is_active'    => true,
        ]);

        // Akun Admin Sarana: Hendra Setiawan (Login via username: 'admin' atau NIP: '198501012010011001', pass: 'password123')
        $akunAdmin = $this->seedAkun([
            'username'     => 'admin',
            'nis'          => null,
            'nip'          => '198501012010011001',
            'nama'         => 'Hendra Setiawan',
            'email'        => 'hendra.setiawan@sinfas.sch.id',
            'nomor_kontak' => '081299887766',
            'role'         => 'admin_sarana',
            'password'     => Hash::make('password123'),
            'is_active'    => true,
        ]);

        // Akun Admin Sistem: Administrator IT (Login via username: 'adminsistem', pass: 'password123')
        $akunAdminSistem = $this->seedAkun([
            'username'     => 'adminsistem',
            'nis'          => null,
            'nip'          => null,
            'nama'         => 'Administrator Sistem',
            'email'        => 'adminsistem@sinfas.sch.id',
            'nomor_kontak' => '081299001122',
            'role'         => 'admin_sistem',
            'password'     => Hash::make('password123'),
            'is_active'    => true,
        ]);

        // =====================================================================
        // 2. Inisialisasi Data Master Siswa (Terkoneksi ke id_akun & kelas ERD 2)
        // =====================================================================
        $siswaData = [
            [
                'nis'     => '10223001',
                'id_akun' => $akunAhmad->id_akun,
                'nama'    => 'Ahmad Fadli',
                'email'   => 'ahmad.fadli@gmail.com',
                'kelas'   => 'XII RPL 1',
                'no_hp'   => '081234567890',
            ],
            [
                'nis'     => '10223002',
                'id_akun' => $akunSiti->id_akun,
                'nama'    => 'Siti Nurhaliza',
                'email'   => 'siti.nurhaliza@gmail.com',
                'kelas'   => 'XII TKJ 2',
                'no_hp'   => '081234567891',
            ],
            [
                'nis'     => '10223003',
                'id_akun' => $akunBudi->id_akun,
                'nama'    => 'Budi Santoso',
                'email'   => 'budi.santoso@gmail.com',
                'kelas'   => 'XI RPL 2',
                'no_hp'   => '081234567892',
            ],
            [
                'nis'     => '10223004',
                'id_akun' => $akunDewi->id_akun,
                'nama'    => 'Dewi Lestari',
                'email'   => 'dewi.lestari@gmail.com',
                'kelas'   => 'XI DKV 1',
                'no_hp'   => '081234567893',
            ],
        ];

        foreach ($siswaData as $item) {
            Siswa::updateOrCreate(['nis' => $item['nis']], $item);
        }

        // =====================================================================
        // 3. Inisialisasi Data Master Pegawai & Staff Sarana (ERD 2)
        // =====================================================================
        $pegawaiData = [
            [
                'nip'     => '198501012010011001',
                'id_akun' => null, // Terhubung ke tabel staff_sarana
                'nama'    => 'Hendra Setiawan',
                'email'   => 'hendra.sarana@sinfas.sch.id',
                'jabatan' => 'Koordinator Sarana Prasarana',
                'no_hp'   => '081299887766',
            ],
            [
                'nip'     => '198802022012022002',
                'id_akun' => null,
                'nama'    => 'Rina Wijaya',
                'email'   => 'rina.wijaya@sinfas.sch.id',
                'jabatan' => 'Guru Produktif RPL',
                'no_hp'   => '081288776655',
            ],
            [
                'nip'     => '199003032015031003',
                'id_akun' => $akunBambang->id_akun,
                'nama'    => 'Bambang Pamungkas',
                'email'   => 'bambang.guru@sinfas.sch.id',
                'jabatan' => 'Guru Pendidikan Jasmani',
                'no_hp'   => '081277665544',
            ],
        ];

        foreach ($pegawaiData as $item) {
            Pegawai::updateOrCreate(['nip' => $item['nip']], $item);
        }

        // Inisialisasi Petugas Sarana (staff_sarana)
        StaffSarana::updateOrCreate(
            ['nip' => '198501012010011001'],
            [
                'id_akun' => $akunAdmin->id_akun,
                'nama'    => 'Hendra Setiawan',
                'no_hp'   => '081299887766',
            ]
        );

        // =====================================================================
        // 4. Inisialisasi Master Kategori & Inventaris Barang
        // =====================================================================
        $this->call(BarangKategoriSeeder::class);

        // =====================================================================
        // 5. Inisialisasi Riwayat Transaksi Peminjaman Dummy (ERD 2)
        // =====================================================================
        $peminjamanData = [
            // Status: Menunggu Verifikasi Admin (Peminjam Siswa)
            [
                'kode_pinjam'           => 'PNJ-2024-001',
                'id_peminjaman'         => 'PNJ-2024-001',
                'id_akun'               => $akunAhmad->id_akun,
                'nis'                   => '10223001',
                'kode_barang'           => 'PRJ-001',
                'tanggal_pinjam'        => '2024-03-15',
                'keterangan_penggunaan' => 'Presentasi tugas akhir kelas XII',
                'lokasi_penggunaan'     => 'Ruang 31',
                'status_pengajuan'      => 'menunggu',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-002',
                'id_peminjaman'         => 'PNJ-2024-002',
                'id_akun'               => $akunSiti->id_akun,
                'nis'                   => '10223002',
                'kode_barang'           => 'AUD-002',
                'tanggal_pinjam'        => '2024-03-16',
                'keterangan_penggunaan' => 'Acara pentas seni sekolah',
                'lokasi_penggunaan'     => 'Aula Utama',
                'status_pengajuan'      => 'menunggu',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-003',
                'id_peminjaman'         => 'PNJ-2024-003',
                'id_akun'               => $akunBudi->id_akun,
                'nis'                   => '10223003',
                'kode_barang'           => 'AUD-001',
                'tanggal_pinjam'        => '2024-03-16',
                'keterangan_penggunaan' => 'Rekaman podcast sekolah',
                'lokasi_penggunaan'     => 'Ruang 7',
                'status_pengajuan'      => 'menunggu',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-004',
                'id_peminjaman'         => 'PNJ-2024-004',
                'id_akun'               => $akunDewi->id_akun,
                'nis'                   => '10223004',
                'kode_barang'           => 'CAM-001',
                'tanggal_pinjam'        => '2024-03-17',
                'keterangan_penggunaan' => 'Dokumentasi kegiatan OSIS',
                'lokasi_penggunaan'     => 'Lapangan Sekolah',
                'status_pengajuan'      => 'menunggu',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-005',
                'id_peminjaman'         => 'PNJ-2024-005',
                'id_akun'               => $akunAhmad->id_akun,
                'nis'                   => '10223001',
                'kode_barang'           => 'PRJ-004',
                'tanggal_pinjam'        => '2024-03-17',
                'keterangan_penggunaan' => 'Latihan presentasi lomba',
                'lokasi_penggunaan'     => 'Ruang Lab',
                'status_pengajuan'      => 'menunggu',
            ],
            // Status: Menunggu Verifikasi Admin (Peminjam Pegawai / Guru - Fitur Baru ERD 2)
            [
                'kode_pinjam'           => 'PNJ-2024-010',
                'id_peminjaman'         => 'PNJ-2024-010',
                'id_akun'               => $akunBambang->id_akun,
                'nis'                   => null,
                'kode_barang'           => 'PRJ-002',
                'tanggal_pinjam'        => '2024-03-18',
                'keterangan_penggunaan' => 'Kegiatan workshop guru kejuruan',
                'lokasi_penggunaan'     => 'Ruang Rapat Guru',
                'status_pengajuan'      => 'menunggu',
            ],
            // Status: Disetujui (Sedang Digunakan / Currently Borrowed)
            [
                'kode_pinjam'           => 'PNJ-2024-006',
                'id_peminjaman'         => 'PNJ-2024-006',
                'id_akun'               => $akunSiti->id_akun,
                'nis'                   => '10223002',
                'kode_barang'           => 'KBL-001',
                'tanggal_pinjam'        => '2024-03-10',
                'keterangan_penggunaan' => 'Setup multimedia kelas',
                'lokasi_penggunaan'     => 'Ruang 12',
                'status_pengajuan'      => 'disetujui',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-007',
                'id_peminjaman'         => 'PNJ-2024-007',
                'id_akun'               => $akunBudi->id_akun,
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
                'id_peminjaman'         => 'PNJ-2024-008',
                'id_akun'               => $akunAhmad->id_akun,
                'nis'                   => '10223001',
                'kode_barang'           => 'PRJ-001',
                'tanggal_pinjam'        => '2024-03-08',
                'keterangan_penggunaan' => 'Presentasi proyek sains',
                'lokasi_penggunaan'     => 'Ruang 5',
                'status_pengajuan'      => 'disetujui',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-009',
                'id_peminjaman'         => 'PNJ-2024-009',
                'id_akun'               => $akunDewi->id_akun,
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
        // 6. Inisialisasi Audit Trail Persetujuan (Penyetujuan ERD 2)
        // =====================================================================
        $penyetujuanData = [
            [
                'id_peminjaman' => 'PNJ-2024-006',
                'nip'           => '198501012010011001',
                'status'        => 'disetujui',
                'catatan'       => 'Peminjaman disetujui untuk kegiatan multimedia kelas.',
            ],
            [
                'id_peminjaman' => 'PNJ-2024-007',
                'nip'           => '198501012010011001',
                'status'        => 'disetujui',
                'catatan'       => 'Peminjaman disetujui untuk tugas prakarya.',
            ],
            [
                'id_peminjaman' => 'PNJ-2024-008',
                'nip'           => '198501012010011001',
                'status'        => 'disetujui',
                'catatan'       => 'Peminjaman disetujui untuk proyek sains.',
            ],
            [
                'id_peminjaman' => 'PNJ-2024-009',
                'nip'           => '198501012010011001',
                'status'        => 'disetujui',
                'catatan'       => 'Peminjaman disetujui untuk kegiatan class meeting.',
            ],
        ];

        foreach ($penyetujuanData as $item) {
            Penyetujuan::updateOrCreate(
                ['id_peminjaman' => $item['id_peminjaman'], 'nip' => $item['nip']],
                $item
            );
        }

        // =====================================================================
        // 7. Inisialisasi Transaksi Pengembalian Dummy
        // =====================================================================
        $pengembalianData = [
            [
                'kode_kembali'     => 'KMB-2024-001',
                'kode_pinjam'      => 'PNJ-2024-008',
                'tanggal_kembali'  => '2024-03-18',
                'kondisi_barang'   => null, // Belum dikonfirmasi admin (tab antrean pengembalian)
                'bukti_foto_video' => null,
            ],
            [
                'kode_kembali'     => 'KMB-2024-002',
                'kode_pinjam'      => 'PNJ-2024-009',
                'tanggal_kembali'  => '2024-03-19',
                'kondisi_barang'   => null, // Belum dikonfirmasi admin (tab antrean pengembalian)
                'bukti_foto_video' => null,
            ],
        ];

        foreach ($pengembalianData as $item) {
            Pengembalian::updateOrCreate(['kode_kembali' => $item['kode_kembali']], $item);
        }
    }

    /**
     * Membantu penyisipan atau pembaruan akun secara idempoten dan aman terhadap constraint unique.
     *
     * @param  array  $attributes
     * @return \App\Models\Akun
     */
    private function seedAkun(array $attributes): Akun
    {
        // Cari apakah akun sudah ada berdasarkan username atau identitas uniknya
        $akun = Akun::where('username', $attributes['username'])->first();

        if (! $akun && ! empty($attributes['nip'])) {
            $akun = Akun::where('nip', $attributes['nip'])
                ->where('role', $attributes['role'])
                ->first();
        }

        if (! $akun && ! empty($attributes['nis'])) {
            $akun = Akun::where('nis', $attributes['nis'])->first();
        }

        if (! $akun && ! empty($attributes['email'])) {
            $akun = Akun::where('email', $attributes['email'])->first();
        }

        if ($akun) {
            // Hindari tabrakan jika email sudah terpakai di record akun lain
            if (! empty($attributes['email']) && $akun->email !== $attributes['email']) {
                $emailExists = Akun::where('email', $attributes['email'])
                    ->where('id_akun', '!=', $akun->id_akun)
                    ->exists();
                if ($emailExists) {
                    unset($attributes['email']);
                }
            }

            // Hindari tabrakan jika username sudah terpakai di record akun lain
            if (! empty($attributes['username']) && $akun->username !== $attributes['username']) {
                $usernameExists = Akun::where('username', $attributes['username'])
                    ->where('id_akun', '!=', $akun->id_akun)
                    ->exists();
                if ($usernameExists) {
                    unset($attributes['username']);
                }
            }

            $akun->update($attributes);
            return $akun;
        }

        return Akun::create($attributes);
    }
}
