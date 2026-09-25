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

        $type = $validated['type'] ?? 'loan-trends';
        $startDate = Carbon::parse($validated['start_date'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($validated['end_date'] ?? now()->toDateString())->endOfDay();
        $categoryId = $validated['category_id'] ?? null;
        $categoryName = $categoryId
            ? Kategori::whereKey($categoryId)->value('nama_kategori')
            : 'Semua kategori';

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
        $chartValues = [];

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

            $monthlyCounts = (clone $query)
                ->select(DB::raw('YEAR(peminjaman.tanggal_pinjam) as tahun'), DB::raw('MONTH(peminjaman.tanggal_pinjam) as bulan'), DB::raw('COUNT(*) as total'))
                ->groupBy('tahun', 'bulan')
                ->orderBy('tahun')
                ->orderBy('bulan')
                ->get()
                ->keyBy(fn ($row) => sprintf('%04d-%02d', $row->tahun, $row->bulan));

            $cursor = $startDate->copy()->startOfMonth();
            while ($cursor <= $endDate) {
                $key = $cursor->format('Y-m');
                $chartLabels[] = $cursor->locale('id')->translatedFormat('M Y');
                $chartValues[] = (int) ($monthlyCounts->get($key)->total ?? 0);
                $cursor->addMonth();
            }
        } elseif ($type === 'damage-history') {
            $loans = Peminjaman::with(['siswa', 'barang.kategori', 'pengembalian'])
                ->whereHas('pengembalian', function ($query) use ($startDate, $endDate, $validated) {
                    $query->whereBetween('tanggal_kembali', [$startDate->toDateString(), $endDate->toDateString()])
                        ->whereIn('kondisi_barang', ['Kurang Baik', 'Rusak Berat'])
                        ->when($validated['condition'] ?? null, fn ($q, $condition) => $q->where('kondisi_barang', $condition));
                })
                ->when($categoryId, fn ($q) => $q->whereHas('barang', fn ($barang) => $barang->where('id_kategori', $categoryId)))
                ->orderByDesc('tanggal_pinjam')
                ->get();

            $columns = ['Kode pinjam', 'Tanggal kembali', 'NIS', 'Nama siswa', 'Barang', 'Kategori', 'Kondisi', 'Catatan'];
            $rows = $loans->map(fn ($loan) => [
                $loan->kode_pinjam,
                optional($loan->pengembalian->tanggal_kembali)->format('d-m-Y'),
                $loan->nis,
                $loan->siswa->nama ?? '-',
                $loan->barang->nama_barang ?? '-',
                $loan->barang->kategori->nama_kategori ?? '-',
                $loan->pengembalian->kondisi_barang,
                $loan->pengembalian->catatan ?: '-',
            ]);
            $summary = ['Barang bermasalah dikembalikan' => $loans->count()];
        } elseif ($type === 'late-returns') {
            $query = Peminjaman::query()
                ->select('peminjaman.*')
                ->leftJoin('pengembalian', 'peminjaman.kode_pinjam', '=', 'pengembalian.kode_pinjam')
                ->with(['siswa', 'barang.kategori', 'pengembalian'])
                ->where('peminjaman.status_pengajuan', 'disetujui')
                ->whereBetween('peminjaman.tanggal_pinjam', [$startDate->toDateString(), $endDate->toDateString()])
                ->whereRaw('DATEDIFF(COALESCE(pengembalian.tanggal_kembali, CURDATE()), DATE_ADD(peminjaman.tanggal_pinjam, INTERVAL ' . self::LOAN_DAYS . ' DAY)) > 0')
                ->when(($validated['return_status'] ?? null) === 'returned', fn ($q) => $q->whereNotNull('pengembalian.kode_kembali'))
                ->when(($validated['return_status'] ?? null) === 'unreturned', fn ($q) => $q->whereNull('pengembalian.kode_kembali'))
                ->when($categoryId, fn ($q) => $q->whereHas('barang', fn ($barang) => $barang->where('id_kategori', $categoryId)))
                ->orderBy('peminjaman.tanggal_pinjam');

            $loans = $query->get();
            $columns = ['Kode pinjam', 'NIS', 'Nama siswa', 'Barang', 'Tanggal pinjam', 'Batas kembali', 'Tanggal kembali', 'Status', 'Terlambat (hari)'];
            $rows = $loans->map(function ($loan) {
                $dueDate = Carbon::parse($loan->tanggal_pinjam)->addDays(self::LOAN_DAYS);
                $returnedDate = $loan->pengembalian?->tanggal_kembali
                    ? Carbon::parse($loan->pengembalian->tanggal_kembali)
                    : Carbon::today();

                return [
                    $loan->kode_pinjam,
                    $loan->nis,
                    $loan->siswa->nama ?? '-',
                    $loan->barang->nama_barang ?? '-',
                    Carbon::parse($loan->tanggal_pinjam)->format('d-m-Y'),
                    $dueDate->format('d-m-Y'),
                    $loan->pengembalian?->tanggal_kembali ? $returnedDate->format('d-m-Y') : '-',
                    $loan->pengembalian ? 'Sudah kembali' : 'Belum kembali',
                    max(0, (int) $dueDate->diffInDays($returnedDate, false)),
                ];
            });
            $summary = ['Peminjaman terlambat' => $loans->count(), 'Total hari keterlambatan' => (int) $rows->sum(fn ($row) => $row[8])];
        } else {
            $items = Barang::with('kategori')
                ->withCount(['peminjaman as sedang_dipinjam_count' => fn ($query) => $query
                    ->where('status_pengajuan', 'disetujui')
                    ->whereDoesntHave('pengembalian', fn ($return) => $return->whereNotNull('kondisi_barang'))])
                ->when($categoryId, fn ($q) => $q->where('id_kategori', $categoryId))
                ->orderBy('nama_barang')
                ->get();

            $columns = ['Kode barang', 'Nama barang', 'Kategori', 'Baik / tersedia', 'Kurang baik', 'Rusak berat', 'Sedang dipinjam', 'Total aset'];
            $rows = $items->map(fn ($item) => [
                $item->kode_barang,
                $item->nama_barang,
                $item->kategori->nama_kategori ?? 'Tanpa kategori',
                (int) $item->jumlah_baik,
                (int) $item->jumlah_kurang_baik,
                (int) $item->jumlah_rusak_berat,
                (int) $item->sedang_dipinjam_count,
                (int) ($item->total_stok + $item->sedang_dipinjam_count),
            ]);
            $summary = [
                'Baik / tersedia' => (int) $items->sum('jumlah_baik'),
                'Kurang baik' => (int) $items->sum('jumlah_kurang_baik'),
                'Rusak berat' => (int) $items->sum('jumlah_rusak_berat'),
                'Sedang dipinjam' => (int) $items->sum('sedang_dipinjam_count'),
                'Total aset' => (int) $rows->sum(fn ($row) => $row[7]),
            ];
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
            'condition' => $validated['condition'] ?? '',
            'returnStatus' => $validated['return_status'] ?? '',
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
        ]);
    }
}
