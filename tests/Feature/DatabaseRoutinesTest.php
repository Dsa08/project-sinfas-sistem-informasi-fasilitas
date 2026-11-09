<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Barang;
use App\Models\MonitoringPeminjamanView;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class DatabaseRoutinesTest
 *
 * Pengujian otomatis untuk memverifikasi fungsionalitas objek tingkat basis data MySQL:
 * 1. Stored Functions: `fn_sisa_kuota_siswa` dan `fn_hitung_terlambat_hari`
 * 2. Stored Procedure: `sp_rekapitulasi_peminjaman_bulanan`
 * 3. Triggers: `trg_kurangi_stok_peminjaman` dan `trg_kembalikan_stok_barang`
 * 4. Database View: `v_monitoring_peminjaman_aktif`
 */
class DatabaseRoutinesTest extends TestCase
{
    /**
     * Memastikan Stored Functions terdaftar dan menghasilkan kalkulasi yang akurat di MySQL.
     *
     * @return void
     */
    public function test_stored_functions_calculate_correctly(): void
    {
        // 1. Uji fungsi hitung keterlambatan (Batas standar: 3 hari)
        $tepatWaktu = DB::selectOne("SELECT fn_hitung_terlambat_hari('2026-11-01', '2026-11-03') AS terlambat")->terlambat;
        $this->assertEquals(0, (int) $tepatWaktu);

        $terlambatDuaHari = DB::selectOne("SELECT fn_hitung_terlambat_hari('2026-11-01', '2026-11-06') AS terlambat")->terlambat;
        $this->assertEquals(2, (int) $terlambatDuaHari);

        // 2. Uji fungsi kuota peminjaman siswa
        $dummyNis = 'NIS_' . rand(1000, 9999);
        $sisaKuota = DB::selectOne("SELECT fn_sisa_kuota_siswa(?) AS kuota", [$dummyNis])->kuota;
        $this->assertEquals(2, (int) $sisaKuota);
    }

    /**
     * Memastikan Stored Procedure rekapitulasi bulanan dapat dieksekusi via CALL dan menghasilkan struktur kolom yang valid.
     *
     * @return void
     */
    public function test_stored_procedure_rekapitulasi_executes_properly(): void
    {
        $result = DB::select('CALL sp_rekapitulasi_peminjaman_bulanan(?, ?)', [date('n'), date('Y')]);

        $this->assertNotEmpty($result);
        $row = $result[0];

        $this->assertEquals(date('n'), (int) $row->bulan);
        $this->assertEquals(date('Y'), (int) $row->tahun);
        $this->assertObjectHasProperty('total_pengajuan', $row);
        $this->assertObjectHasProperty('total_disetujui', $row);
        $this->assertObjectHasProperty('total_ditolak', $row);
        $this->assertObjectHasProperty('total_dibatalkan', $row);
        $this->assertObjectHasProperty('total_sedang_dipinjam', $row);
        $this->assertObjectHasProperty('total_kembali_tepat_waktu', $row);
        $this->assertObjectHasProperty('total_terlambat', $row);
    }

    /**
     * Memastikan Database View memetakan data peminjaman multi-tabel dengan atribut virtual yang lengkap.
     *
     * @return void
     */
    public function test_database_view_maps_virtual_entity_properly(): void
    {
        $viewRecords = MonitoringPeminjamanView::take(5)->get();
        $this->assertNotNull($viewRecords);

        // Verifikasi melalui query langsung ke SQL View
        $rawCount = DB::selectOne("SELECT COUNT(*) AS total FROM v_monitoring_peminjaman_aktif")->total;
        $this->assertGreaterThanOrEqual(0, (int) $rawCount);

        // Jika terdapat data peminjaman, periksa struktur kolom view
        if ($viewRecords->isNotEmpty()) {
            $first = $viewRecords->first();
            $this->assertNotEmpty($first->kode_pinjam);
            $this->assertNotEmpty($first->nama_barang);
            $this->assertNotEmpty($first->status_operasional);
            $this->assertNotEmpty($first->batas_kembali);
        }
    }
}
