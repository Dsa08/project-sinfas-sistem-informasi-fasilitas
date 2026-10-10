<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Peminjaman;
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
            'loan-trends' => 'Frekuensi & Tren Peminjaman Barang',
            'damage-history' => 'Kerusakan & Riwayat Kondisi Barang',
            'late-returns' => 'Keterlambatan Pengembalian Barang',
            'stock-summary' => 'Rekapitulasi Stok & Status Inventaris',
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
            $columns = ['Kode barang', 'Nama barang', 'Kategori', 'Frekuensi dipinjam'];
            $summary = ['Total transaksi' => (int) $itemCounts->sum('total_peminjaman'), 'Barang dipinjam' => $itemCounts->count()];
            $chartIndexAxis = 'y';
            $chartItems = $itemCounts->take(10);
            $chartTitle = '10 Barang Paling Sering Dipinjam';
            $chartDescription = 'Peringkat frekuensi peminjaman pada rentang dan kategori yang dipilih.';
            $chartLabels = $chartItems->pluck('nama_barang')->all();
            $chartDatasets[] = [
                'label' => 'Frekuensi dipinjam',
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

            $columns = ['Kode pinjam', 'Tanggal kembali', 'NIS/NIP', 'Nama peminjam', 'Barang', 'Kategori', 'Kondisi', 'Catatan'];
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
            $summary = ['Barang bermasalah dikembalikan' => $loans->count()];
            $chartTitle = 'Barang Dikembalikan Berdasarkan Kondisi';
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
                ->with(['akun', 'siswa', 'barang.kategori', 'pengembalian'])
                ->where('peminjaman.status_pengajuan', 'disetujui')
                ->whereBetween('peminjaman.tanggal_pinjam', [$startDate->toDateString(), $endDate->toDateString()])
                ->whereRaw('DATEDIFF(COALESCE(pengembalian.tanggal_kembali, CURDATE()), DATE_ADD(peminjaman.tanggal_pinjam, INTERVAL ' . self::LOAN_DAYS . ' DAY)) > 0')
                ->when($returnStatus === 'returned', fn ($q) => $q->whereNotNull('pengembalian.kode_kembali'))
                ->when($returnStatus === 'unreturned', fn ($q) => $q->whereNull('pengembalian.kode_kembali'))
                ->when($categoryId, fn ($q) => $q->whereHas('barang', fn ($barang) => $barang->where('id_kategori', $categoryId)))
                ->orderBy('peminjaman.tanggal_pinjam');

            $loans = $query->get();
            $columns = ['Kode pinjam', 'NIS/NIP', 'Nama peminjam', 'Barang', 'Tanggal pinjam', 'Batas kembali', 'Tanggal kembali', 'Status', 'Terlambat (hari)'];
            $rows = $loans->map(function ($loan) {
                $dueDate = Carbon::parse($loan->tanggal_pinjam)->addDays(self::LOAN_DAYS);
                $returnedDate = $loan->pengembalian?->tanggal_kembali
                    ? Carbon::parse($loan->pengembalian->tanggal_kembali)
                    : Carbon::today();

                return [
                    $loan->kode_pinjam,
                    $loan->akun?->nis_nip ?? $loan->nis ?? '-',
                    $loan->peminjam_nama,
                    $loan->barang->nama_barang ?? '-',
                    Carbon::parse($loan->tanggal_pinjam)->format('d-m-Y'),
                    $dueDate->format('d-m-Y'),
                    $loan->pengembalian?->tanggal_kembali ? $returnedDate->format('d-m-Y') : '-',
                    $loan->pengembalian ? 'Sudah kembali' : 'Belum kembali',
                    max(0, (int) $dueDate->diffInDays($returnedDate, false)),
                ];
            });
            $summary = ['Peminjaman terlambat' => $loans->count(), 'Total hari keterlambatan' => (int) $rows->sum(fn ($row) => $row[8])];
            $chartTitle = 'Kelompok Hari Keterlambatan';
            $chartDescription = 'Jumlah peminjaman berdasarkan lama keterlambatan pada data yang ditampilkan.';
            $chartLabels = ['1-3 hari', '4-7 hari', 'Lebih dari 7 hari'];
            $chartDatasets[] = [
                'label' => 'Jumlah peminjaman',
                'data' => [
                    $rows->filter(fn ($row) => $row[8] >= 1 && $row[8] <= 3)->count(),
                    $rows->filter(fn ($row) => $row[8] >= 4 && $row[8] <= 7)->count(),
                    $rows->filter(fn ($row) => $row[8] > 7)->count(),
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

            $columns = ['Kode barang', 'Nama barang', 'Kategori', 'Baik / tersedia', 'Kurang baik', 'Rusak berat', 'Total aset'];
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
                'Baik / tersedia' => (int) $items->sum('jumlah_baik'),
                'Kurang baik' => (int) $items->sum('jumlah_kurang_baik'),
                'Rusak berat' => (int) $items->sum('jumlah_rusak_berat'),
                'Total aset' => (int) $rows->sum(fn ($row) => $row[6]),
            ];
            $chartTitle = 'Komposisi Stok Inventaris';
            $chartDescription = 'Jumlah stok menurut kondisi barang untuk kategori yang dipilih.';
            $chartLabels = ['Baik / tersedia', 'Kurang baik', 'Rusak berat'];
            $chartDatasets[] = [
                'label' => 'Jumlah stok',
                'data' => [$summary['Baik / tersedia'], $summary['Kurang baik'], $summary['Rusak berat']],
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
}
