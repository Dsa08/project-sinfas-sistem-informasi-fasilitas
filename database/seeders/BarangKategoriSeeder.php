<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;
use App\Models\Barang;

class BarangKategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Definisikan 5 Kategori
        $kategoriList = [
            'Audio & Sound System',
            'Proyektor & Presentasi',
            'Kamera & Dokumentasi',
            'Kabel & Adapter',
            'Peralatan Lab & Multimedia',
        ];

        $kategoriMap = [];
        foreach ($kategoriList as $nama) {
            $cat = Kategori::updateOrCreate(
                ['nama_kategori' => $nama],
                ['nama_kategori' => $nama]
            );
            $kategoriMap[$nama] = $cat->id_kategori;
        }

        // 2. Definisikan 5 Barang untuk masing-masing dari 5 Kategori (Total 25 Barang)
        $barangList = [
            // ==========================================
            // Kategori 1: Audio & Sound System (5 Barang)
            // ==========================================
            [
                'kode_barang'        => 'AUD-001',
                'id_kategori'        => $kategoriMap['Audio & Sound System'],
                'nama_barang'        => 'Microphone Wireless Clip-on Boya',
                'merk_model'         => 'Boya BY-WM4 Pro',
                'no_seri_pabrik'     => 'BY-WM4-0192',
                'ukuran_dimensi'     => '8 x 3 x 2 cm',
                'bahan'              => 'Plastik ABS',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 8,
                'jumlah_kurang_baik' => 2,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Termasuk 1 transmitter, 1 receiver, dan hardcase bawaan',
            ],
            [
                'kode_barang'        => 'AUD-002',
                'id_kategori'        => $kategoriMap['Audio & Sound System'],
                'nama_barang'        => 'Speaker Portable Bluetooth JBL',
                'merk_model'         => 'JBL Charge 5',
                'no_seri_pabrik'     => 'JBL-CHG5-8821',
                'ukuran_dimensi'     => '22 x 9.6 x 9.3 cm',
                'bahan'              => 'Plastik & Silikon Rubber',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 4,
                'jumlah_kurang_baik' => 1,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Daya tahan baterai hingga 20 jam, tahan air IP67',
            ],
            [
                'kode_barang'        => 'AUD-003',
                'id_kategori'        => $kategoriMap['Audio & Sound System'],
                'nama_barang'        => 'Megaphone Pengeras Suara TOA',
                'merk_model'         => 'TOA ZR-206W',
                'no_seri_pabrik'     => 'TOA-ZR206-331',
                'ukuran_dimensi'     => '21 x 21 x 38 cm',
                'bahan'              => 'Plastik ABS Ringan',
                'tahun_pembelian'    => 2022,
                'jumlah_baik'        => 3,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Dilengkapi fungsi sirine dan pengatur volume suara',
            ],
            [
                'kode_barang'        => 'AUD-004',
                'id_kategori'        => $kategoriMap['Audio & Sound System'],
                'nama_barang'        => 'Portable Sound System Trolley Baretone',
                'merk_model'         => 'Baretone MAX-12AL',
                'no_seri_pabrik'     => 'BRT-MAX12-104',
                'ukuran_dimensi'     => '38 x 33 x 58 cm',
                'bahan'              => 'Kayu Partikel & Logam',
                'tahun_pembelian'    => 2022,
                'jumlah_baik'        => 2,
                'jumlah_kurang_baik' => 1,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Speaker woofer 12 inch, 2 mic wireless genggam, baterai isi ulang',
            ],
            [
                'kode_barang'        => 'AUD-005',
                'id_kategori'        => $kategoriMap['Audio & Sound System'],
                'nama_barang'        => 'Headphone Studio Monitoring Audio-Technica',
                'merk_model'         => 'Audio-Technica ATH-M20x',
                'no_seri_pabrik'     => 'ATH-M20X-552',
                'ukuran_dimensi'     => '20 x 18 x 9 cm',
                'bahan'              => 'Polycarbonate & Kulit Sintetis',
                'tahun_pembelian'    => 2024,
                'jumlah_baik'        => 5,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Driver 40mm tuned suara jernih, kabel panjang 3 meter + adapter 6.3mm',
            ],

            // ==========================================
            // Kategori 2: Proyektor & Presentasi (5 Barang)
            // ==========================================
            [
                'kode_barang'        => 'PRJ-001',
                'id_kategori'        => $kategoriMap['Proyektor & Presentasi'],
                'nama_barang'        => 'Proyektor Epson 3600 Lumens',
                'merk_model'         => 'Epson EB-X500',
                'no_seri_pabrik'     => 'EPS-X500-7712',
                'ukuran_dimensi'     => '30 x 23.7 x 8.2 cm',
                'bahan'              => 'Plastik ABS + Logam',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 6,
                'jumlah_kurang_baik' => 1,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Resolusi XGA, port HDMI dan VGA, lampu tahan hingga 12.000 jam',
            ],
            [
                'kode_barang'        => 'PRJ-002',
                'id_kategori'        => $kategoriMap['Proyektor & Presentasi'],
                'nama_barang'        => 'Proyektor Portable BenQ DLP',
                'merk_model'         => 'BenQ MS550',
                'no_seri_pabrik'     => 'BNQ-MS550-990',
                'ukuran_dimensi'     => '29.6 x 12 x 22.1 cm',
                'bahan'              => 'Plastik Polimer',
                'tahun_pembelian'    => 2022,
                'jumlah_baik'        => 3,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 1,
                'keterangan'         => '3600 ANSI Lumens, dual port HDMI, hemat daya SmartEco',
            ],
            [
                'kode_barang'        => 'PRJ-003',
                'id_kategori'        => $kategoriMap['Proyektor & Presentasi'],
                'nama_barang'        => 'Layar Proyektor Tripod 70 Inch',
                'merk_model'         => 'ScreenBeam Tripod 70',
                'no_seri_pabrik'     => 'SCR-TR70-044',
                'ukuran_dimensi'     => '178 x 178 cm',
                'bahan'              => 'Matte White Fabric & Logam Baja',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 4,
                'jumlah_kurang_baik' => 1,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Tinggi stand dapat diatur, kain layar tebal anti silau',
            ],
            [
                'kode_barang'        => 'PRJ-004',
                'id_kategori'        => $kategoriMap['Proyektor & Presentasi'],
                'nama_barang'        => 'Wireless Presenter Laser Pointer Logitech',
                'merk_model'         => 'Logitech R400',
                'no_seri_pabrik'     => 'LOG-R400-610',
                'ukuran_dimensi'     => '11.5 x 3.8 x 2.7 cm',
                'bahan'              => 'Plastik Ringan',
                'tahun_pembelian'    => 2024,
                'jumlah_baik'        => 7,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Sinar laser merah fokus, tombol slide intuitif, jangkauan 15 meter',
            ],
            [
                'kode_barang'        => 'PRJ-005',
                'id_kategori'        => $kategoriMap['Proyektor & Presentasi'],
                'nama_barang'        => 'Smart Mini LED Projector Wanbo',
                'merk_model'         => 'Wanbo T2 Max New',
                'no_seri_pabrik'     => 'WNB-T2M-1102',
                'ukuran_dimensi'     => '11.3 x 14.6 x 15.6 cm',
                'bahan'              => 'Plastik Matte',
                'tahun_pembelian'    => 2024,
                'jumlah_baik'        => 0,
                'jumlah_kurang_baik' => 2,
                'jumlah_rusak_berat' => 1,
                'keterangan'         => 'Resolusi native Full HD 1080p, sistem Android terintegrasi (Stok habis/servis)',
            ],

            // ==========================================
            // Kategori 3: Kamera & Dokumentasi (5 Barang)
            // ==========================================
            [
                'kode_barang'        => 'CAM-001',
                'id_kategori'        => $kategoriMap['Kamera & Dokumentasi'],
                'nama_barang'        => 'Kamera DSLR Canon EOS 3000D',
                'merk_model'         => 'Canon EOS 3000D Kit 18-55mm',
                'no_seri_pabrik'     => 'CAN-3000D-512',
                'ukuran_dimensi'     => '12.9 x 10.1 x 7.7 cm',
                'bahan'              => 'Polycarbonate Resin',
                'tahun_pembelian'    => 2022,
                'jumlah_baik'        => 3,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Sensor 18MP APS-C, lensa kit 18-55mm, SD Card 32GB dan charger asli',
            ],
            [
                'kode_barang'        => 'CAM-002',
                'id_kategori'        => $kategoriMap['Kamera & Dokumentasi'],
                'nama_barang'        => 'Kamera Mirrorless Sony Alpha A6400',
                'merk_model'         => 'Sony ILCE-6400',
                'no_seri_pabrik'     => 'SNY-A6400-883',
                'ukuran_dimensi'     => '12 x 6.7 x 5.9 cm',
                'bahan'              => 'Magnesium Alloy',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 2,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Sensor 24.2MP, perekaman video 4K HDR, flip screen 180 derajat untuk vlog',
            ],
            [
                'kode_barang'        => 'CAM-003',
                'id_kategori'        => $kategoriMap['Kamera & Dokumentasi'],
                'nama_barang'        => 'Tripod Kamera Profesional Takara',
                'merk_model'         => 'Takara ECO-196A',
                'no_seri_pabrik'     => 'TKR-196A-201',
                'ukuran_dimensi'     => '55 - 145 cm',
                'bahan'              => 'Aluminium Alloy',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 5,
                'jumlah_kurang_baik' => 1,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Tinggi maksimal 145 cm, kapasitas beban 3 kg, sudah termasuk tas jinjing',
            ],
            [
                'kode_barang'        => 'CAM-004',
                'id_kategori'        => $kategoriMap['Kamera & Dokumentasi'],
                'nama_barang'        => 'Ring Light LED Studio 26cm + Stand',
                'merk_model'         => 'Midio RL-26 Dual Tone',
                'no_seri_pabrik'     => 'MID-RL26-440',
                'ukuran_dimensi'     => 'Diameter 26 cm, Stand 2.1 m',
                'bahan'              => 'Plastik ABS & Metal Stand',
                'tahun_pembelian'    => 2024,
                'jumlah_baik'        => 6,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Pilihan 3 mode warna (putih, natural, warm) dengan holder smartphone',
            ],
            [
                'kode_barang'        => 'CAM-005',
                'id_kategori'        => $kategoriMap['Kamera & Dokumentasi'],
                'nama_barang'        => 'Gimbal Stabilizer Smartphone DJI',
                'merk_model'         => 'DJI Osmo Mobile 6',
                'no_seri_pabrik'     => 'DJI-OM6-719',
                'ukuran_dimensi'     => '27.6 x 11.1 x 9.9 cm',
                'bahan'              => 'Plastik Polimer & Logam Ringan',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 2,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Stabilisasi 3-axis, ActiveTrack 5.0, built-in extension rod teleskopik',
            ],

            // ==========================================
            // Kategori 4: Kabel & Adapter (5 Barang)
            // ==========================================
            [
                'kode_barang'        => 'KBL-001',
                'id_kategori'        => $kategoriMap['Kabel & Adapter'],
                'nama_barang'        => 'Kabel HDMI High Speed 10 Meter',
                'merk_model'         => 'Vention Ultra HD 4K',
                'no_seri_pabrik'     => 'VNT-HDMI10-09',
                'ukuran_dimensi'     => 'Panjang 10 Meter',
                'bahan'              => 'Tembaga Murni + Braided Nylon',
                'tahun_pembelian'    => 2024,
                'jumlah_baik'        => 10,
                'jumlah_kurang_baik' => 2,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Mendukung output visual 4K pada frekuensi 60Hz, konektor lapis emas',
            ],
            [
                'kode_barang'        => 'KBL-002',
                'id_kategori'        => $kategoriMap['Kabel & Adapter'],
                'nama_barang'        => 'Adapter USB Type-C ke HDMI 5-in-1',
                'merk_model'         => 'Ugreen CM179 Multiport Hub',
                'no_seri_pabrik'     => 'UGR-CM179-88',
                'ukuran_dimensi'     => '12 x 3.5 x 1.5 cm',
                'bahan'              => 'Aluminium Case Pembuang Panas',
                'tahun_pembelian'    => 2024,
                'jumlah_baik'        => 6,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Port HDMI 4K, 3 port USB 3.0, serta port pengisian daya PD 100W',
            ],
            [
                'kode_barang'        => 'KBL-003',
                'id_kategori'        => $kategoriMap['Kabel & Adapter'],
                'nama_barang'        => 'Kabel Roll Stopkontak Uticon 15M',
                'merk_model'         => 'Uticon Heavy Duty CR-2815',
                'no_seri_pabrik'     => 'UTI-CR2815-12',
                'ukuran_dimensi'     => 'Panjang 15 Meter',
                'bahan'              => 'Kabel Tembaga + Plastik Tahan Panas',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 5,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => '4 lubang colokan steker dengan pemutus arus otomatis dan switch lampu',
            ],
            [
                'kode_barang'        => 'KBL-004',
                'id_kategori'        => $kategoriMap['Kabel & Adapter'],
                'nama_barang'        => 'Kabel VGA to VGA Male-Male 5M',
                'merk_model'         => 'Bafo Premium VGA Cable 5M',
                'no_seri_pabrik'     => 'BAF-VGA5M-301',
                'ukuran_dimensi'     => 'Panjang 5 Meter',
                'bahan'              => 'PVC Tebal & Ferrite Core',
                'tahun_pembelian'    => 2021,
                'jumlah_baik'        => 4,
                'jumlah_kurang_baik' => 2,
                'jumlah_rusak_berat' => 1,
                'keterangan'         => 'Konektor 15-pin berpelindung ganda untuk mencegah noise distorsi gambar',
            ],
            [
                'kode_barang'        => 'KBL-005',
                'id_kategori'        => $kategoriMap['Kabel & Adapter'],
                'nama_barang'        => 'Converter DisplayPort ke HDMI 4K',
                'merk_model'         => 'Baseus High-Speed DP-HDMI',
                'no_seri_pabrik'     => 'BAS-DPHDMI-41',
                'ukuran_dimensi'     => '20 cm kabel adapter',
                'bahan'              => 'Aluminium Alloy + Braided',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 4,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Konversi sinyal kartu grafis PC Desktop DisplayPort menuju monitor HDMI',
            ],

            // ==========================================
            // Kategori 5: Peralatan Lab & Multimedia (5 Barang)
            // ==========================================
            [
                'kode_barang'        => 'LAB-001',
                'id_kategori'        => $kategoriMap['Peralatan Lab & Multimedia'],
                'nama_barang'        => 'Laptop Inventaris Praktikum Lenovo ThinkPad',
                'merk_model'         => 'Lenovo ThinkPad E14 Gen 4',
                'no_seri_pabrik'     => 'LNV-E14G4-081',
                'ukuran_dimensi'     => '32.4 x 22 x 1.8 cm',
                'bahan'              => 'Aluminium Anodized + ABS',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 5,
                'jumlah_kurang_baik' => 1,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Intel Core i5 Gen 12, RAM 16GB, SSD 512GB, OS Windows 11 Pro terpasang',
            ],
            [
                'kode_barang'        => 'LAB-002',
                'id_kategori'        => $kategoriMap['Peralatan Lab & Multimedia'],
                'nama_barang'        => 'Drawing Tablet Pen Wacom One',
                'merk_model'         => 'Wacom One Small CTL-472',
                'no_seri_pabrik'     => 'WCM-CTL472-91',
                'ukuran_dimensi'     => '21 x 14.6 x 0.8 cm',
                'bahan'              => 'Plastik Polimer Ringan',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 6,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Tekanan pena 2048 level, stylus tanpa baterai, responsif untuk desain grafis',
            ],
            [
                'kode_barang'        => 'LAB-003',
                'id_kategori'        => $kategoriMap['Peralatan Lab & Multimedia'],
                'nama_barang'        => 'Barcode Scanner USB Wireless Zebra',
                'merk_model'         => 'Zebra DS2208 Handheld 2D',
                'no_seri_pabrik'     => 'ZBR-DS2208-11',
                'ukuran_dimensi'     => '16.5 x 6.6 x 9.9 cm',
                'bahan'              => 'Plastik Industri Tahan Benturan',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 3,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Pemindai kode batang 1D dan QR Code 2D secara cepat dan akurat',
            ],
            [
                'kode_barang'        => 'LAB-004',
                'id_kategori'        => $kategoriMap['Peralatan Lab & Multimedia'],
                'nama_barang'        => 'Webcam Full HD 1080P Logitech Stream',
                'merk_model'         => 'Logitech C922 Pro Stream',
                'no_seri_pabrik'     => 'LOG-C922-302',
                'ukuran_dimensi'     => '9.5 x 7.1 x 4.4 cm',
                'bahan'              => 'Plastik & Lensa Kaca Full HD',
                'tahun_pembelian'    => 2024,
                'jumlah_baik'        => 4,
                'jumlah_kurang_baik' => 0,
                'jumlah_rusak_berat' => 0,
                'keterangan'         => 'Perekaman 1080p 30fps / 720p 60fps, mikrofon ganda, dilengkapi mini tripod',
            ],
            [
                'kode_barang'        => 'LAB-005',
                'id_kategori'        => $kategoriMap['Peralatan Lab & Multimedia'],
                'nama_barang'        => 'Harddisk Eksternal 2TB Seagate One Touch',
                'merk_model'         => 'Seagate One Touch 2TB USB 3.0',
                'no_seri_pabrik'     => 'SGT-OT2TB-554',
                'ukuran_dimensi'     => '11.5 x 8 x 1.2 cm',
                'bahan'              => 'Aluminium Brush Metal',
                'tahun_pembelian'    => 2023,
                'jumlah_baik'        => 0,
                'jumlah_kurang_baik' => 1,
                'jumlah_rusak_berat' => 1,
                'keterangan'         => 'Kapasitas penyimpanan 2TB dengan proteksi enkripsi (Stok habis/dalam perbaikan)',
            ],
        ];

        foreach ($barangList as $item) {
            Barang::updateOrCreate(
                ['kode_barang' => $item['kode_barang']],
                $item
            );
        }
    }
}
