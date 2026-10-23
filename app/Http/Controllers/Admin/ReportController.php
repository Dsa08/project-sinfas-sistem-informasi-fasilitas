<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Services\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private const LOAN_DAYS = 3;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'type' => 'nullable|in:loan-trends,damage-history,late-returns,stock-summary',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'category_id' => 'nullable|integer|exists:kategori,id_kategori',
            'condition' => 'nullable|in:Kurang Baik,Rusak Berat',
            'return_status' => 'nullable|in:returned,unreturned',
        ]);

        $previousSettings = $request->session()->get('admin_report_settings', []);
        $type = $validated['type'] ?? $previousSettings['type'] ?? 'loan-trends';
        $startDate = Carbon::parse($validated['start_date'] ?? $previousSettings['start_date'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($validated['end_date'] ?? $previousSettings['end_date'] ?? now()->toDateString())->endOfDay();
        $categoryId = array_key_exists('category_id', $validated)
            ? $validated['category_id']
            : ($previousSettings['category_id'] ?? null);
        $condition = array_key_exists('condition', $validated)
            ? $validated['condition']
            : ($previousSettings['condition'] ?? null);
        $returnStatus = array_key_exists('return_status', $validated)
            ? $validated['return_status']
            : ($previousSettings['return_status'] ?? null);
        $categoryName = $categoryId
            ? Kategori::whereKey($categoryId)->value('nama_kategori')
            : 'Semua kategori';

        $request->session()->put('admin_report_settings', [
            'type' => $type,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'category_id' => $categoryId,
            'condition' => $condition,
            'return_status' => $returnStatus,
        ]);

        $types = [
            'loan-trends' => 'Frekuensi & Tren Peminjaman Alat',
            'damage-history' => 'Kerusakan & Riwayat Kondisi Alat',
            'late-returns' => 'Keterlambatan Pengembalian Alat',
            'stock-summary' => 'Rekapitulasi Stok & Status Inventaris Alat',
        ];
        $title = $types[$type];
        $columns = [];
        $rows = collect();
        $summary = [];
        $chartLabels = [];
        $chartDatasets = [];
        $chartType = 'bar';
        $chartIndexAxis = 'x';
        $chartTitle = '';
        $chartDescription = '';

        if ($type === 'loan-trends') {
            $query = DB::table('peminjaman')
                ->join('barang', 'peminjaman.kode_barang', '=', 'barang.kode_barang')
                ->leftJoin('kategori', 'barang.id_kategori', '=', 'kategori.id_kategori')
                ->whereBetween('peminjaman.tanggal_pinjam', [$startDate->toDateString(), $endDate->toDateString()])
                ->when($categoryId, fn ($q) => $q->where('barang.id_kategori', $categoryId));

            $itemCounts = (clone $query)
                ->select('barang.kode_barang', 'barang.nama_barang', 'kategori.nama_kategori', DB::raw('COUNT(*) as total_peminjaman'))
                ->groupBy('barang.kode_barang', 'barang.nama_barang', 'kategori.nama_kategori')
                ->orderByDesc('total_peminjaman')
                ->orderBy('barang.nama_barang')
                ->get();

            $rows = $itemCounts->map(fn ($item) => [
                $item->kode_barang,
                $item->nama_barang,
                $item->nama_kategori ?? 'Tanpa kategori',
                (int) $item->total_peminjaman,
            ]);
            $columns = ['Kode Alat', 'Nama Alat', 'Kategori', 'Frekuensi Dipinjam'];
            $summary = ['Total transaksi' => (int) $itemCounts->sum('total_peminjaman'), 'Alat dipinjam' => $itemCounts->count()];
            $chartIndexAxis = 'y';
            $chartItems = $itemCounts->take(10);
            $chartTitle = '10 Alat Paling Sering Dipinjam';
            $chartDescription = 'Peringkat frekuensi peminjaman pada rentang dan kategori yang dipilih.';
            $chartLabels = $chartItems->pluck('nama_barang')->all();
            $chartDatasets[] = [
                'label' => 'Frekuensi Dipinjam',
                'data' => $chartItems->pluck('total_peminjaman')->map(fn ($count) => (int) $count)->all(),
                'backgroundColor' => array_slice(['#f97316', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899', '#14b8a6', '#eab308', '#6366f1', '#84cc16', '#06b6d4'], 0, $chartItems->count()),
                'borderColor' => array_slice(['#c2410c', '#1d4ed8', '#047857', '#6d28d9', '#be185d', '#0f766e', '#a16207', '#4338ca', '#4d7c0f', '#0e7490'], 0, $chartItems->count()),
                'borderWidth' => 1,
                'borderRadius' => 5,
                'barThickness' => 34,
                'categoryPercentage' => 0.9,
                'barPercentage' => 0.9,
            ];
        } elseif ($type === 'damage-history') {
            $loans = Peminjaman::with(['akun', 'siswa', 'barang.kategori', 'pengembalian'])
                ->whereHas('pengembalian', function ($query) use ($startDate, $endDate, $condition) {
                    $query->whereBetween('tanggal_kembali', [$startDate->toDateString(), $endDate->toDateString()])
                        ->whereIn('kondisi_barang', ['Kurang Baik', 'Rusak Berat'])
                        ->when($condition, fn ($q, $condition) => $q->where('kondisi_barang', $condition));
                })
                ->when($categoryId, fn ($q) => $q->whereHas('barang', fn ($barang) => $barang->where('id_kategori', $categoryId)))
                ->orderByDesc('tanggal_pinjam')
                ->get();

            $columns = ['Kode Pinjam', 'Tanggal Kembali', 'NIS/NIP', 'Nama Peminjam', 'Alat', 'Kategori', 'Kondisi', 'Catatan'];
            $rows = $loans->map(fn ($loan) => [
                $loan->kode_pinjam,
                optional($loan->pengembalian->tanggal_kembali)->format('d-m-Y'),
                $loan->akun?->nis_nip ?? $loan->nis ?? '-',
                $loan->peminjam_nama,
                $loan->barang->nama_barang ?? '-',
                $loan->barang->kategori->nama_kategori ?? '-',
                $loan->pengembalian->kondisi_barang,
                $loan->pengembalian->catatan ?: '-',
            ]);
            $summary = ['Alat bermasalah dikembalikan' => $loans->count()];
            $chartTitle = 'Alat Dikembalikan Berdasarkan Kondisi';
            $chartDescription = 'Jumlah pengembalian bermasalah sesuai kategori dan periode yang dipilih.';
            $chartLabels = ['Kurang Baik', 'Rusak Berat'];
            $chartDatasets[] = [
                'label' => 'Jumlah pengembalian',
                'data' => [
                    $loans->filter(fn ($loan) => $loan->pengembalian->kondisi_barang === 'Kurang Baik')->count(),
                    $loans->filter(fn ($loan) => $loan->pengembalian->kondisi_barang === 'Rusak Berat')->count(),
                ],
                'backgroundColor' => ['#f59e0b', '#ef4444'],
                'borderColor' => '#ffffff',
                'borderWidth' => 2,
            ];
            $chartType = 'doughnut';
        } elseif ($type === 'late-returns') {
            $query = Peminjaman::query()
                ->select('peminjaman.*')
                ->leftJoin('pengembalian', 'peminjaman.kode_pinjam', '=', 'pengembalian.kode_pinjam')
                ->with(['akun.siswa', 'akun.pegawai', 'siswa', 'barang.kategori', 'pengembalian'])
                ->where('peminjaman.status_pengajuan', 'disetujui')
                ->whereBetween('peminjaman.tanggal_pinjam', [$startDate->toDateString(), $endDate->toDateString()])
                ->whereRaw('DATEDIFF(COALESCE(pengembalian.tanggal_kembali, CURDATE()), DATE_ADD(peminjaman.tanggal_pinjam, INTERVAL ' . self::LOAN_DAYS . ' DAY)) > 0')
                ->when($returnStatus === 'returned', fn ($q) => $q->whereNotNull('pengembalian.kode_kembali'))
                ->when($returnStatus === 'unreturned', fn ($q) => $q->whereNull('pengembalian.kode_kembali'))
                ->when($categoryId, fn ($q) => $q->whereHas('barang', fn ($barang) => $barang->where('id_kategori', $categoryId)))
                ->orderBy('peminjaman.tanggal_pinjam');

            $loans = $query->get();

            $lateLoans = $loans->map(function ($loan) {
                $dueDate = Carbon::parse($loan->tanggal_pinjam)->addDays(self::LOAN_DAYS);
                $returnedDate = $loan->pengembalian?->tanggal_kembali
                    ? Carbon::parse($loan->pengembalian->tanggal_kembali)
                    : Carbon::today();
                $lateDays = max(0, (int) $dueDate->diffInDays($returnedDate, false));

                $isSiswa = $loan->akun?->isSiswa() || filled($loan->nis);
                $kelas = $loan->akun?->siswa?->kelas ?? $loan->siswa?->kelas;
                $jabatan = $loan->akun?->pegawai?->jabatan;
                $kelasOrJabatan = $kelas ?: ($jabatan ?: ($isSiswa ? 'Siswa' : 'Pegawai'));

                $kontak = $loan->akun?->nomor_kontak ?: ($loan->siswa?->no_hp ?: ($loan->akun?->pegawai?->no_hp ?: ''));

                return [
                    'kode_pinjam' => $loan->kode_pinjam,
                    'nis_nip' => $loan->akun?->nis_nip ?? $loan->nis ?? '-',
                    'nama_peminjam' => $loan->peminjam_nama,
                    'role_label' => $loan->peminjam_status,
                    'is_siswa' => $isSiswa,
                    'kelas' => $kelas,
                    'jabatan' => $jabatan,
                    'kelas_or_jabatan' => $kelasOrJabatan,
                    'kontak' => $kontak ?: '-',
                    'barang' => $loan->barang->nama_barang ?? '-',
                    'kategori' => $loan->barang->kategori->nama_kategori ?? '-',
                    'tanggal_pinjam' => Carbon::parse($loan->tanggal_pinjam)->format('d-m-Y'),
                    'batas_kembali' => $dueDate->format('d-m-Y'),
                    'tanggal_kembali' => $loan->pengembalian?->tanggal_kembali ? $returnedDate->format('d-m-Y') : '-',
                    'status' => $loan->pengembalian ? 'Sudah kembali' : 'Belum kembali',
                    'is_returned' => (bool) $loan->pengembalian,
                    'late_days' => $lateDays,
                ];
            });

            // Grouping peminjam: urutkan data agar peminjam yang sama berkumpul bersama
            $lateLoans = $lateLoans->sortBy(fn ($item) => strtolower($item['nama_peminjam']))->values();

            $columns = ['Kode Pinjam', 'NIS / NIP', 'Nama Peminjam', 'Kelas / Jabatan', 'Kontak', 'Alat', 'Tanggal Pinjam', 'Batas Kembali', 'Tanggal Kembali', 'Status', 'Terlambat (Hari)', 'Aksi'];
            $rows = $lateLoans->map(function ($item) {
                return [
                    $item['kode_pinjam'],
                    $item['nis_nip'],
                    $item['nama_peminjam'],
                    $item['kelas_or_jabatan'],
                    $item['kontak'],
                    $item['barang'],
                    $item['tanggal_pinjam'],
                    $item['batas_kembali'],
                    $item['tanggal_kembali'],
                    $item['status'],
                    $item['late_days'] . ' hari',
                    $item['is_returned'] ? '-' : 'Ingatkan In-App',
                ];
            });

            $uniqueBorrowers = $lateLoans->pluck('nama_peminjam')->unique()->count();
            $unreturnedCount = $lateLoans->where('is_returned', false)->count();
            $summary = [
                'Peminjam terlambat' => $uniqueBorrowers,
                'Belum kembali' => $unreturnedCount,
                'Total transaksi terlambat' => $lateLoans->count(),
                'Total hari keterlambatan' => (int) $lateLoans->sum('late_days'),
            ];

            $chartTitle = 'Kelompok Hari Keterlambatan';
            $chartDescription = 'Jumlah peminjaman berdasarkan lama keterlambatan pada data yang ditampilkan.';
            $chartLabels = ['1-3 hari', '4-7 hari', 'Lebih dari 7 hari'];
            $chartDatasets[] = [
                'label' => 'Jumlah peminjaman',
                'data' => [
                    $lateLoans->filter(fn ($item) => $item['late_days'] >= 1 && $item['late_days'] <= 3)->count(),
                    $lateLoans->filter(fn ($item) => $item['late_days'] >= 4 && $item['late_days'] <= 7)->count(),
                    $lateLoans->filter(fn ($item) => $item['late_days'] > 7)->count(),
                ],
                'backgroundColor' => ['#facc15', '#f97316', '#ef4444'],
                'borderColor' => ['#ca8a04', '#c2410c', '#b91c1c'],
                'borderWidth' => 1,
                'borderRadius' => 5,
                'barThickness' => 52,
            ];
        } else {
            $items = Barang::with('kategori')
                ->when($categoryId, fn ($q) => $q->where('id_kategori', $categoryId))
                ->orderBy('nama_barang')
                ->get();

            $columns = ['Kode Alat', 'Nama Alat', 'Kategori', 'Baik / Tersedia', 'Kurang Baik', 'Rusak Berat', 'Total Aset'];
            $rows = $items->map(fn ($item) => [
                $item->kode_barang,
                $item->nama_barang,
                $item->kategori->nama_kategori ?? 'Tanpa kategori',
                (int) $item->jumlah_baik,
                (int) $item->jumlah_kurang_baik,
                (int) $item->jumlah_rusak_berat,
                (int) $item->total_stok,
            ]);
            $summary = [
                'Baik / Tersedia' => (int) $items->sum('jumlah_baik'),
                'Kurang Baik' => (int) $items->sum('jumlah_kurang_baik'),
                'Rusak Berat' => (int) $items->sum('jumlah_rusak_berat'),
                'Total Aset' => (int) $rows->sum(fn ($row) => $row[6]),
            ];
            $chartTitle = 'Komposisi Stok Inventaris';
            $chartDescription = 'Jumlah stok menurut kondisi alat untuk kategori yang dipilih.';
            $chartLabels = ['Baik / Tersedia', 'Kurang Baik', 'Rusak Berat'];
            $chartDatasets[] = [
                'label' => 'Jumlah stok',
                'data' => [$summary['Baik / Tersedia'], $summary['Kurang Baik'], $summary['Rusak Berat']],
                'backgroundColor' => ['#22c55e', '#f59e0b', '#ef4444'],
                'borderColor' => '#ffffff',
                'borderWidth' => 2,
            ];
            $chartType = 'doughnut';
        }

        return view('admin.reports.index', [
            'type' => $type,
            'types' => $types,
            'title' => $title,
            'columns' => $columns,
            'rows' => $rows,
            'lateLoans' => $lateLoans ?? null,
            'summary' => $summary,
            'categories' => Kategori::orderBy('nama_kategori')->get(),
            'categoryId' => $categoryId,
            'categoryName' => $categoryName,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'condition' => $condition ?? '',
            'returnStatus' => $returnStatus ?? '',
            'chartType' => $chartType,
            'chartIndexAxis' => $chartIndexAxis,
            'chartTitle' => $chartTitle,
            'chartDescription' => $chartDescription,
            'chartLabels' => $chartLabels,
            'chartDatasets' => $chartDatasets,
        ]);
    }

    /**
     * Mengirimkan notifikasi in-app pengingat keterlambatan kepada peminjam sarana.
     *
     * @param  string  $kode  Kode transaksi peminjaman
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function notifyOverdue(string $kode, Request $request)
    {
        $peminjaman = Peminjaman::with(['akun', 'barang', 'pengembalian'])
            ->where('kode_pinjam', $kode)
            ->firstOrFail();

        if ($peminjaman->pengembalian && $peminjaman->pengembalian->tanggal_kembali) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang ini sudah dikembalikan.',
                ], 422);
            }

            return back()->with('error', 'Barang ini sudah dikembalikan.');
        }

        $dueDate = Carbon::parse($peminjaman->tanggal_pinjam)->addDays(self::LOAN_DAYS);
        $lateDays = max(0, (int) $dueDate->diffInDays(Carbon::today(), false));

        // Kirim notifikasi in-app ke akun peminjam
        NotifikasiService::peringatanBatasWaktu($peminjaman, $lateDays);

        $borrowerName = $peminjaman->peminjam_nama;
        $message = "Notifikasi pengingat in-app berhasil dikirim ke akun {$borrowerName}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
