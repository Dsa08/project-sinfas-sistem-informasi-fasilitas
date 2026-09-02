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
        // 4. Data Dummy Kategori
        // ================================================
        $kategoriData = [
            ['nama_kategori' => 'Mic'],
            ['nama_kategori' => 'Kabel HDMI'],
            ['nama_kategori' => 'Proyektor'],
            ['nama_kategori' => 'Converter'],
            ['nama_kategori' => 'Speaker'],
            ['nama_kategori' => 'Kamera'],
            ['nama_kategori' => 'Tripod'],
        ];

        foreach ($kategoriData as $item) {
            Kategori::updateOrCreate(['nama_kategori' => $item['nama_kategori']], $item);
        }

        // Fetch kategori IDs
        $catMic       = Kategori::where('nama_kategori', 'Mic')->first()->id_kategori;
        $catHdmi      = Kategori::where('nama_kategori', 'Kabel HDMI')->first()->id_kategori;
        $catProyektor = Kategori::where('nama_kategori', 'Proyektor')->first()->id_kategori;
        $catConverter = Kategori::where('nama_kategori', 'Converter')->first()->id_kategori;
        $catSpeaker   = Kategori::where('nama_kategori', 'Speaker')->first()->id_kategori;
        $catKamera    = Kategori::where('nama_kategori', 'Kamera')->first()->id_kategori;
        $catTripod    = Kategori::where('nama_kategori', 'Tripod')->first()->id_kategori;

        // ================================================
        // 5. Data Dummy Barang
        // ================================================
        $barangData = [
            [
                'kode_barang'        => 'BRG-001',
                'id_kategori'        => $catMic,
                'nama_barang'        => 'Microphone Wireless Clip-on',
                'merk_model'         => 'Boya BY-WM4',
                'no_seri_pabrik'     => 'BY-2024-001',
                'ukuran_dimensi'     => '8 x 3 x 2 cm',
                'bahan'              => 'Plastik ABS',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 8,
                'jumlah_kurang_baik' => 3,
                'jumlah_rusak_berat' => 1,
                'keterangan'         => 'Termasuk receiver dan kabel charging',
            ],
            [
                'kode_barang'        => 'BRG-002',
                'id_kategori'        => $catHdmi,
                'nama_barang'        => 'Kabel HDMI 10 Meter',
                'merk_model'         => 'Vention HDMI 2.0',
                'no_seri_pabrik'     => 'VN-HDMI-10M',
                'ukuran_dimensi'     => '10 m',
                'bahan'              => 'Tembaga + Nylon',
                'tahun_pembelian'    => 2024,
                'jumlah_baik'        => 5,
                'jumlah_kurang_baik' => 2,
                'jumlah_rusak_berat' => 1,
                'keterangan'         => 'Support 4K 60Hz',
            ],
            [
                'kode_barang'        => 'BRG-003',
                'id_kategori'        => $catProyektor,
                'nama_barang'        => 'Proyektor Epson X300',
                'merk_model'         => 'Epson EB-X300',
                'no_seri_pabrik'     => 'EPS-X300-0088',
                'ukuran_dimensi'     => '30 x 23 x 9 cm',
                'bahan'              => 'Plastik + Metal',
                'tahun_pembelian'    => 2022,
                'jumlah_baik'        => 3,
                'jumlah_kurang_baik' => 1,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => '3600 Lumens, XGA Resolution',
            ],
            [
                'kode_barang'        => 'BRG-004',
                'id_kategori'        => $catConverter,
                'nama_barang'        => 'Converter USB-C to HDMI',
                'merk_model'         => 'Ugreen CM159',
                'no_seri_pabrik'     => 'UG-CM159-003',
                'ukuran_dimensi'     => '12 x 3 x 1.5 cm',
                'bahan'              => 'Aluminium',
                'tahun_pembelian'    => 2024,
                'jumlah_baik'        => 3,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => '4K@60Hz, kompatibel MacBook & laptop USB-C',
            ],
            [
                'kode_barang'        => 'BRG-005',
                'id_kategori'        => $catSpeaker,
                'nama_barang'        => 'Portable Speaker JBL',
                'merk_model'         => 'JBL Charge 5',
                'no_seri_pabrik'     => 'JBL-CHG5-007',
                'ukuran_dimensi'     => '22 x 9.6 x 9.3 cm',
                'bahan'              => 'Plastik + Karet',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 2,
                'jumlah_kurang_baik' => 1,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Tahan air IP67, Bluetooth 5.1',
            ],
            [
                'kode_barang'        => 'BRG-006',
                'id_kategori'        => $catKamera,
                'nama_barang'        => 'Kamera DSLR Canon 3000D',
                'merk_model'         => 'Canon EOS 3000D',
                'no_seri_pabrik'     => 'CAN-3000D-015',
                'ukuran_dimensi'     => '12.9 x 10.1 x 7.7 cm',
                'bahan'              => 'Metal + Polycarbonate',
                'tahun_pembelian'    => 2022,
                'jumlah_baik'        => 2,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 1,
                'keterangan'         => 'Kit Lens 18-55mm, 18MP APS-C',
            ],
            [
                'kode_barang'        => 'BRG-007',
                'id_kategori'        => $catTripod,
                'nama_barang'        => 'Tripod Kamera Takara',
                'merk_model'         => 'Takara ECO-196A',
                'no_seri_pabrik'     => 'TKR-196A-010',
                'ukuran_dimensi'     => '55 - 145 cm',
                'bahan'              => 'Aluminium',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 4,
                'jumlah_kurang_baik' => 1,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Max load 3kg, dilengkapi tas carry',
            ],
            [
                'kode_barang'        => 'BRG-008',
                'id_kategori'        => $catMic,
                'nama_barang'        => 'Wireless Presenter Laser',
                'merk_model'         => 'Logitech R400',
                'no_seri_pabrik'     => 'LGT-R400-022',
                'ukuran_dimensi'     => '11.5 x 3.3 x 2.4 cm',
                'bahan'              => 'Plastik ABS',
                'tahun_pembelian'    => 2024,
                'jumlah_baik'        => 4,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Laser merah, jarak hingga 15m',
            ],
        ];

        foreach ($barangData as $item) {
            Barang::updateOrCreate(['kode_barang' => $item['kode_barang']], $item);
        }

        // ================================================
        // 6. Data Dummy Peminjaman (beberapa menunggu, beberapa disetujui)
        // ================================================
        $peminjamanData = [
            // Menunggu verifikasi
            [
                'kode_pinjam'           => 'PNJ-2024-001',
                'nis'                   => '10223001',
                'kode_barang'           => 'BRG-003',
                'tanggal_pinjam'        => '2024-03-15',
                'keterangan_penggunaan' => 'Presentasi tugas akhir kelas XII',
                'lokasi_penggunaan'     => 'Ruang 31',
                'status_pengajuan'      => 'menunggu',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-002',
                'nis'                   => '10223002',
                'kode_barang'           => 'BRG-005',
                'tanggal_pinjam'        => '2024-03-16',
                'keterangan_penggunaan' => 'Acara pentas seni sekolah',
                'lokasi_penggunaan'     => 'Aula Utama',
                'status_pengajuan'      => 'menunggu',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-003',
                'nis'                   => '10223003',
                'kode_barang'           => 'BRG-001',
                'tanggal_pinjam'        => '2024-03-16',
                'keterangan_penggunaan' => 'Rekaman podcast sekolah',
                'lokasi_penggunaan'     => 'Ruang 7',
                'status_pengajuan'      => 'menunggu',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-004',
                'nis'                   => '10223004',
                'kode_barang'           => 'BRG-006',
                'tanggal_pinjam'        => '2024-03-17',
                'keterangan_penggunaan' => 'Dokumentasi kegiatan OSIS',
                'lokasi_penggunaan'     => 'Lapangan Sekolah',
                'status_pengajuan'      => 'menunggu',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-005',
                'nis'                   => '10223001',
                'kode_barang'           => 'BRG-008',
                'tanggal_pinjam'        => '2024-03-17',
                'keterangan_penggunaan' => 'Latihan presentasi lomba',
                'lokasi_penggunaan'     => 'Ruang Lab',
                'status_pengajuan'      => 'menunggu',
            ],
            // Disetujui (belum dikembalikan → "Currently Borrowed")
            [
                'kode_pinjam'           => 'PNJ-2024-006',
                'nis'                   => '10223002',
                'kode_barang'           => 'BRG-002',
                'tanggal_pinjam'        => '2024-03-10',
                'keterangan_penggunaan' => 'Setup multimedia kelas',
                'lokasi_penggunaan'     => 'Ruang 12',
                'status_pengajuan'      => 'disetujui',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-007',
                'nis'                   => '10223003',
                'kode_barang'           => 'BRG-007',
                'tanggal_pinjam'        => '2024-03-12',
                'keterangan_penggunaan' => 'Foto produk untuk tugas prakarya',
                'lokasi_penggunaan'     => 'Studio Mini',
                'status_pengajuan'      => 'disetujui',
            ],
            // Disetujui + sudah ada pengembalian (pending confirm return)
            [
                'kode_pinjam'           => 'PNJ-2024-008',
                'nis'                   => '10223001',
                'kode_barang'           => 'BRG-003',
                'tanggal_pinjam'        => '2024-03-08',
                'keterangan_penggunaan' => 'Presentasi proyek sains',
                'lokasi_penggunaan'     => 'Ruang 5',
                'status_pengajuan'      => 'disetujui',
            ],
            [
                'kode_pinjam'           => 'PNJ-2024-009',
                'nis'                   => '10223004',
                'kode_barang'           => 'BRG-005',
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
