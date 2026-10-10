<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Controller AdminSaranaController
 * 
 * Mengelola seluruh operasional inventaris sarana dan prasarana sekolah:
 * 1. Dashboard Analitik: Metrik sarana, peringkat peminjaman, dan grafik yang mengikuti filter laporan terakhir.
 * 2. Manajemen Master Barang: CRUD inventaris sarana, pengelolaan kondisi fisik (baik, kurang baik, rusak berat),
 *    serta upload berkas foto sarana ke public/uploads/items.
 * 3. Manajemen Master Kategori: CRUD kategori sarana dan invalidasi cache otomatis.
 * 4. Alur Verifikasi Transaksi: Persetujuan peminjaman (approve), penolakan beralasan (reject),
 *    dan konfirmasi penerimaan fisik barang kembali serta penyesuaian otomatis stok inventaris.
 */
class AdminSaranaController extends Controller
{
    // =========================================================================
    //  1. DASHBOARD & ANALITIK SARANA
    // =========================================================================

    /**
     * Menampilkan dashboard analitik admin sarana prasarana.
     * Mengkalkulasi ringkasan statistik (pending, total barang, dipinjam, rusak)
     * serta data tren peminjaman 6 bulan terakhir untuk grafik Chart.js.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        $pendingCount = Peminjaman::menunggu()->count();
        $totalItems = Barang::count();
        $borrowedCount = Peminjaman::disetujui()
            ->whereDoesntHave('pengembalian')
            ->count();
        $damagedCount = Barang::sum('jumlah_rusak_berat');

        $reportSettings = $request->session()->get('admin_report_settings', []);
        $reportTypes = ['loan-trends', 'damage-history', 'late-returns', 'stock-summary'];
        $reportType = in_array($reportSettings['type'] ?? null, $reportTypes, true)
            ? $reportSettings['type']
            : 'loan-trends';
        $startDate = Carbon::parse($reportSettings['start_date'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($reportSettings['end_date'] ?? now()->toDateString())->endOfDay();
        $categoryId = $reportSettings['category_id'] ?? null;
        $condition = $reportSettings['condition'] ?? null;
        $returnStatus = $reportSettings['return_status'] ?? null;
        $categoryName = $categoryId
            ? (Kategori::whereKey($categoryId)->value('nama_kategori') ?? 'Kategori tidak ditemukan')
            : 'Semua kategori';

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
        $months = [];
        $cursor = $startDate->copy()->startOfMonth();
        while ($cursor <= $endDate) {
            $months[] = [
                'key' => $cursor->format('Y-m'),
                'label' => ($monthNames[$cursor->month] ?? $cursor->format('M')) . ' ' . $cursor->year,
            ];
            $cursor->addMonth();
        }
        $chartLabels = array_column($months, 'label');
        $chartType = 'line';
        $chartIndexAxis = 'x';
        $chartTitle = 'Tren Frekuensi Peminjaman';
        $chartDescription = 'Jumlah peminjaman per bulan untuk ' . $categoryName . '.';
        $chartDatasets = [];

        $loanQuery = DB::table('peminjaman')
            ->join('barang', 'peminjaman.kode_barang', '=', 'barang.kode_barang')
            ->leftJoin('kategori', 'barang.id_kategori', '=', 'kategori.id_kategori')
            ->whereBetween('peminjaman.tanggal_pinjam', [$startDate->toDateString(), $endDate->toDateString()])
            ->when($categoryId, fn ($query) => $query->where('barang.id_kategori', $categoryId));

        $topLoanItems = (clone $loanQuery)
            ->select('barang.kode_barang', 'barang.nama_barang', 'kategori.nama_kategori', DB::raw('COUNT(*) as total_peminjaman'))
            ->groupBy('barang.kode_barang', 'barang.nama_barang', 'kategori.nama_kategori')
            ->orderByDesc('total_peminjaman')
            ->orderBy('barang.nama_barang')
            ->limit(5)
            ->get();

        if ($reportType === 'loan-trends') {
            $chartType = 'bar';
            $chartIndexAxis = 'y';
            $chartTitle = 'Frekuensi Peminjaman Barang Teratas';
            $chartDescription = 'Jumlah peminjaman per barang, sesuai tabel peringkat.';
            $chartLabels = $topLoanItems->pluck('nama_barang')->all();
            $itemColors = ['#f97316', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899'];
            $chartDatasets[] = [
                'label' => 'Frekuensi dipinjam',
                'data' => $topLoanItems->pluck('total_peminjaman')->map(fn ($count) => (int) $count)->all(),
                'backgroundColor' => array_slice($itemColors, 0, $topLoanItems->count()),
                'borderColor' => array_slice(['#c2410c', '#1d4ed8', '#047857', '#6d28d9', '#be185d'], 0, $topLoanItems->count()),
                'borderWidth' => 1,
                'borderRadius' => 4,
                'barThickness' => 40,
                'categoryPercentage' => 0.9,
                'barPercentage' => 0.9,
            ];
        } elseif ($reportType === 'damage-history') {
            $chartType = 'bar';
            $chartTitle = 'Tren Kerusakan Barang';
            $chartDescription = 'Barang yang dikembalikan dalam kondisi bermasalah selama periode laporan.';
            $damageRows = DB::table('pengembalian')
                ->join('peminjaman', 'pengembalian.kode_pinjam', '=', 'peminjaman.kode_pinjam')
                ->join('barang', 'peminjaman.kode_barang', '=', 'barang.kode_barang')
                ->whereBetween('pengembalian.tanggal_kembali', [$startDate->toDateString(), $endDate->toDateString()])
                ->whereIn('pengembalian.kondisi_barang', ['Kurang Baik', 'Rusak Berat'])
                ->when($categoryId, fn ($query) => $query->where('barang.id_kategori', $categoryId))
                ->when($condition, fn ($query) => $query->where('pengembalian.kondisi_barang', $condition))
                ->selectRaw('YEAR(pengembalian.tanggal_kembali) as tahun, MONTH(pengembalian.tanggal_kembali) as bulan, pengembalian.kondisi_barang, COUNT(*) as total')
                ->groupBy('tahun', 'bulan', 'pengembalian.kondisi_barang')
                ->get();

            foreach ([
                'Kurang Baik' => '#f59e0b',
                'Rusak Berat' => '#ef4444',
            ] as $damageCondition => $color) {
                $conditionRows = $damageRows->where('kondisi_barang', $damageCondition)
                    ->keyBy(fn ($row) => sprintf('%04d-%02d', $row->tahun, $row->bulan));
                $chartDatasets[] = [
                    'label' => $damageCondition,
                    'data' => array_map(fn ($month) => (int) ($conditionRows->get($month['key'])?->total ?? 0), $months),
                    'backgroundColor' => $color,
                    'borderRadius' => 4,
                ];
            }
        } elseif ($reportType === 'late-returns') {
            $chartType = 'bar';
            $chartTitle = 'Tren Keterlambatan Pengembalian';
            $chartDescription = 'Jumlah peminjaman terlambat per bulan sesuai filter laporan.';
            $lateQuery = DB::table('peminjaman')
                ->leftJoin('pengembalian', 'peminjaman.kode_pinjam', '=', 'pengembalian.kode_pinjam')
                ->join('barang', 'peminjaman.kode_barang', '=', 'barang.kode_barang')
                ->where('peminjaman.status_pengajuan', 'disetujui')
                ->whereBetween('peminjaman.tanggal_pinjam', [$startDate->toDateString(), $endDate->toDateString()])
                ->whereRaw('DATEDIFF(COALESCE(pengembalian.tanggal_kembali, CURDATE()), DATE_ADD(peminjaman.tanggal_pinjam, INTERVAL 3 DAY)) > 0')
                ->when($categoryId, fn ($query) => $query->where('barang.id_kategori', $categoryId))
                ->when($returnStatus === 'returned', fn ($query) => $query->whereNotNull('pengembalian.kode_kembali'))
                ->when($returnStatus === 'unreturned', fn ($query) => $query->whereNull('pengembalian.kode_kembali'));
            $lateCounts = $lateQuery
                ->selectRaw('YEAR(peminjaman.tanggal_pinjam) as tahun, MONTH(peminjaman.tanggal_pinjam) as bulan, COUNT(*) as total')
                ->groupBy('tahun', 'bulan')
                ->get()
                ->keyBy(fn ($row) => sprintf('%04d-%02d', $row->tahun, $row->bulan));

            $chartDatasets[] = [
                'label' => 'Peminjaman terlambat',
                'data' => array_map(fn ($month) => (int) ($lateCounts->get($month['key'])?->total ?? 0), $months),
                'backgroundColor' => '#f97316',
                'borderRadius' => 4,
            ];
        } else {
            $chartType = 'doughnut';
            $chartTitle = 'Komposisi Stok Inventaris';
            $chartDescription = 'Jumlah stok menurut kondisi barang untuk ' . $categoryName . '.';
            $stockTotals = Barang::query()
                ->when($categoryId, fn ($query) => $query->where('id_kategori', $categoryId))
                ->selectRaw('COALESCE(SUM(jumlah_baik), 0) as baik, COALESCE(SUM(jumlah_kurang_baik), 0) as kurang_baik, COALESCE(SUM(jumlah_rusak_berat), 0) as rusak_berat')
                ->first();
            $chartLabels = ['Baik / tersedia', 'Kurang baik', 'Rusak berat'];
            $chartDatasets[] = [
                'label' => 'Jumlah stok',
                'data' => [(int) $stockTotals->baik, (int) $stockTotals->kurang_baik, (int) $stockTotals->rusak_berat],
                'backgroundColor' => ['#22c55e', '#f59e0b', '#ef4444'],
                'borderColor' => '#ffffff',
                'borderWidth' => 2,
            ];
        }

        return view('admin.dashboard', compact(
            'pendingCount',
            'totalItems',
            'borrowedCount',
            'damagedCount',
            'topLoanItems',
            'chartType',
            'chartIndexAxis',
            'chartTitle',
            'chartDescription',
            'chartLabels',
            'chartDatasets',
            'reportType',
            'startDate',
            'endDate',
            'categoryName'
        ));
    }
    // =========================================================================
    //  2. KELOLA DATA MASTER BARANG (SARANA PRASARANA)
    // =========================================================================

    /**
     * Menampilkan daftar katalog barang dengan filter pencarian dan paginasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function items(Request $request)
    {
        $query = Barang::with('kategori');

        // Pencarian multi-kolom
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('merk_model', 'like', "%{$search}%")
                  ->orWhereHas('kategori', function ($kq) use ($search) {
                      $kq->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }

        // Filter berdasarkan kategori sarana
        if ($kategori = $request->input('kategori')) {
            $query->where('id_kategori', $kategori);
        }

        // 3. Penyortiran kolom (Sort by field & direction): default data paling baru di atas (updated_at desc)
        $sort = $request->input('sort');
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['nama_barang', 'kategori', 'waktu_ditambahkan', 'jumlah_baik', 'jumlah_kurang_baik', 'jumlah_rusak_berat', 'status'];

        if ($sort === 'kategori') {
            $query->leftJoin('kategori', 'barang.id_kategori', '=', 'kategori.id_kategori')
                  ->select('barang.*')
                  ->orderBy('kategori.nama_kategori', $dir);
        } elseif ($sort === 'waktu_ditambahkan') {
            $query->orderBy('barang.created_at', $dir);
        } elseif (in_array($sort, $allowedSorts)) {
            $query->orderBy("barang.{$sort}", $dir);
        } else {
            $query->orderBy('barang.updated_at', 'desc');
        }

        $perPage = in_array((int)$request->input('per_page'), [10, 25, 50, 100]) ? (int)$request->input('per_page') : 10;
        $items = $query->paginate($perPage)->withQueryString();
        $categories = Cache::remember('all_categories', 3600, function () {
            return Kategori::orderBy('nama_kategori')->get();
        });

        return view('admin.items', compact('items', 'categories'));
    }

    /**
     * Menyimpan data item sarana prasarana baru ke inventaris.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeItem(Request $request)
    {
        $request->validate([
            'kode_barang'        => 'required|string|max:30|unique:barang,kode_barang',
            'id_kategori'        => 'required|exists:kategori,id_kategori',
            'nama_barang'        => 'required|string|max:255',
            'merk_model'         => 'nullable|string|max:255',
            'no_seri_pabrik'     => 'nullable|string|max:255',
            'ukuran_dimensi'     => 'nullable|string|max:255',
            'bahan'              => 'nullable|string|max:255',
            'tahun_pembelian'    => 'nullable|integer|min:1901|max:' . (date('Y') + 1),
            'jumlah_baik'        => 'required|integer|min:0',
            'jumlah_kurang_baik' => 'required|integer|min:0',
            'jumlah_rusak_berat' => 'required|integer|min:0',
            'keterangan'         => 'nullable|string',
            'foto'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'kode_barang.required' => 'Kode barang wajib diisi.',
            'kode_barang.unique'   => 'Kode barang sudah digunakan.',
            'id_kategori.required' => 'Kategori wajib dipilih.',
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'foto.image'           => 'File harus berupa gambar.',
            'foto.max'             => 'Ukuran gambar maksimal 2MB.',
            'tahun_pembelian.min'  => 'Tahun pembelian minimal 1901.',
            'tahun_pembelian.max'  => 'Tahun pembelian tidak boleh melebihi tahun depan.',
        ]);

        $data = $request->except(['foto']);
        // Pastikan nilai tahun_pembelian yang kosong diubah menjadi NULL, bukan integer 0 atau string kosong
        $data['tahun_pembelian'] = !empty($data['tahun_pembelian']) ? (int) $data['tahun_pembelian'] : null;

        // Penanganan upload berkas gambar sarana
        if ($request->hasFile('foto')) {
            $filename = app(\App\Services\WebpImageOptimizer::class)
                ->storeOnDisk($request->file('foto'), 'items', 'public_uploads', 'item');
            $filename = basename($filename);
            $data['foto'] = 'uploads/items/' . $filename;
        }

        try {
            Barang::create($data);

            return redirect()->route('admin.items')
                ->with('toast_title', 'Penambahan data barang berhasil')
                ->with('toast_message', "Data barang {$data['nama_barang']} berhasil ditambahkan.")
                ->with('success', 'Penambahan data barang berhasil');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal menambahkan barang.', ['exception' => $e]);
            return back()->withInput()
                ->with('toast_type', 'error')
                ->with('toast_title', 'Gagal menambahkan barang')
                ->with('toast_message', 'Barang gagal ditambahkan. Periksa kembali data yang diisi, lalu coba lagi.')
                ->with('error', 'Gagal menambahkan barang');
        }
    }

    /**
     * Mengambil detail satu barang dalam format respons JSON untuk modal AJAX edit barang.
     *
     * @param  string  $kode  Kode barang
     * @return \Illuminate\Http\JsonResponse
     */
    public function showItem($kode)
    {
        $item = Barang::with('kategori')->findOrFail($kode);

        return response()->json([
            'kode_barang'        => $item->kode_barang,
            'id_kategori'        => $item->id_kategori,
            'nama_barang'        => $item->nama_barang,
            'merk_model'         => $item->merk_model,
            'no_seri_pabrik'     => $item->no_seri_pabrik,
            'ukuran_dimensi'     => $item->ukuran_dimensi,
            'bahan'              => $item->bahan,
            'tahun_pembelian'    => $item->tahun_pembelian,
            'jumlah_baik'        => $item->jumlah_baik,
            'jumlah_kurang_baik' => $item->jumlah_kurang_baik,
            'jumlah_rusak_berat' => $item->jumlah_rusak_berat,
            'keterangan'         => $item->keterangan,
            'kategori_nama'      => $item->kategori->nama_kategori ?? '-',
            'status'             => $item->status,
            'foto'               => $item->foto_url,
        ]);
    }

    /**
     * Memperbarui informasi dan stok kondisi sarana prasarana.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $kode  Kode barang
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateItem(Request $request, $kode)
    {
        $item = Barang::findOrFail($kode);

        $request->validate([
            'id_kategori'        => 'required|exists:kategori,id_kategori',
            'nama_barang'        => 'required|string|max:255',
            'merk_model'         => 'nullable|string|max:255',
            'no_seri_pabrik'     => 'nullable|string|max:255',
            'ukuran_dimensi'     => 'nullable|string|max:255',
            'bahan'              => 'nullable|string|max:255',
            'tahun_pembelian'    => 'nullable|integer|min:1901|max:' . (date('Y') + 1),
            'jumlah_baik'        => 'required|integer|min:0',
            'jumlah_kurang_baik' => 'required|integer|min:0',
            'jumlah_rusak_berat' => 'required|integer|min:0',
            'keterangan'         => 'nullable|string',
            'foto'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'id_kategori.required' => 'Kategori wajib dipilih.',
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'foto.image'           => 'File harus berupa gambar.',
            'foto.max'             => 'Ukuran gambar maksimal 2MB.',
            'tahun_pembelian.min'  => 'Tahun pembelian minimal 1901.',
            'tahun_pembelian.max'  => 'Tahun pembelian tidak boleh melebihi tahun depan.',
        ]);

        $data = $request->except(['kode_barang', 'foto']);
        // Pastikan nilai tahun_pembelian yang kosong diubah menjadi NULL, bukan integer 0 atau string kosong
        $data['tahun_pembelian'] = !empty($data['tahun_pembelian']) ? (int) $data['tahun_pembelian'] : null;

        // Mengganti berkas gambar jika pengguna mengunggah berkas baru
        if ($request->hasFile('foto')) {
            $filename = app(\App\Services\WebpImageOptimizer::class)
                ->storeOnDisk($request->file('foto'), 'items', 'public_uploads', 'item');
            $filename = basename($filename);

            // Hapus foto lama setelah foto pengganti berhasil disimpan.
            app(\App\Services\PublicUploadStorage::class)->delete($item->foto);
            $data['foto'] = 'uploads/items/' . $filename;
        }

        try {
            $item->update($data);

            return redirect()->route('admin.items')
                ->with('toast_title', 'Perubahan data barang berhasil')
                ->with('toast_message', "Data barang {$item->nama_barang} berhasil diperbarui.")
                ->with('success', 'Perubahan data barang berhasil');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal memperbarui barang.', ['exception' => $e]);
            return back()->withInput()
                ->with('toast_type', 'error')
                ->with('toast_title', 'Gagal memperbarui barang')
                ->with('toast_message', 'Perubahan barang gagal disimpan. Periksa kembali data yang diisi, lalu coba lagi.')
                ->with('error', 'Gagal memperbarui barang');
        }
    }

    /**
     * Menghapus sarana prasarana dari sistem inventaris.
     * Mencegah penghapusan jika ada transaksi peminjaman aktif yang belum selesai.
     *
     * @param  string  $kode  Kode barang
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyItem($kode)
    {
        $item = Barang::findOrFail($kode);

        // Validasi Integritas Data: pastikan tidak ada peminjaman aktif
        $activeLoan = Peminjaman::where('kode_barang', $kode)
            ->whereIn('status_pengajuan', ['menunggu', 'disetujui'])
            ->whereDoesntHave('pengembalian')
            ->exists();

        if ($activeLoan) {
            return back()
                ->with('toast_title', 'Penghapusan data barang gagal')
                ->with('toast_message', 'Barang tidak dapat dihapus karena masih ada transaksi peminjaman aktif.')
                ->with('error', 'Barang tidak dapat dihapus karena masih ada peminjaman aktif.');
        }

        // Hapus file fisik gambar jika tersimpan
        app(\App\Services\PublicUploadStorage::class)->delete($item->foto);

        $item->delete();

        return redirect()->route('admin.items')
            ->with('toast_title', 'Penghapusan data barang berhasil')
            ->with('toast_message', 'Barang berhasil dihapus.')
            ->with('success', 'Penghapusan data barang berhasil');
    }

    // =========================================================================
    //  3. KELOLA DATA MASTER KATEGORI
    // =========================================================================

    /**
     * Menampilkan daftar kategori sarana beserta jumlah sarana yang terasosiasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function categories(Request $request)
    {
        $query = Kategori::withCount('barang');

        if ($search = $request->input('search')) {
            $query->where('nama_kategori', 'like', "%{$search}%");
        }

        // Sort: default data paling baru di atas (updated_at desc)
        $sort = $request->input('sort');
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sort === 'barang_count') {
            $query->orderBy('barang_count', $dir);
        } elseif ($sort === 'nama_kategori') {
            $query->orderBy('nama_kategori', $dir);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        $perPage = in_array((int)$request->input('per_page'), [10, 25, 50, 100]) ? (int)$request->input('per_page') : 10;
        $categories = $query->paginate($perPage)->withQueryString();

        return view('admin.categories', compact('categories'));
    }

    /**
     * Menyimpan kategori baru ke database dan membersihkan cache kategori.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Nama kategori sudah ada.',
        ]);

        Kategori::create($request->only('nama_kategori'));
        Cache::forget('all_categories');

        return redirect()->route('admin.categories')
            ->with('toast_title', 'Penambahan kategori berhasil')
            ->with('toast_message', "Kategori baru berhasil ditambahkan.")
            ->with('success', 'Penambahan kategori berhasil');
    }

    /**
     * Memperbarui nama kategori dan merefresh cache kategori.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  ID Kategori
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCategory(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id . ',id_kategori',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Nama kategori sudah ada.',
        ]);

        $kategori->update($request->only('nama_kategori'));
        Cache::forget('all_categories');

        return redirect()->route('admin.categories')
            ->with('toast_title', 'Perubahan kategori berhasil')
            ->with('toast_message', "Kategori berhasil diperbarui.")
            ->with('success', 'Perubahan kategori berhasil');
    }

    /**
     * Menghapus kategori sarana jika tidak memiliki barang inventaris yang terhubung.
     *
     * @param  int  $id  ID Kategori
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyCategory($id)
    {
        $kategori = Kategori::withCount('barang')->findOrFail($id);

        if ($kategori->barang_count > 0) {
            return back()
                ->with('toast_title', 'Penghapusan kategori gagal')
                ->with('toast_message', 'Kategori tidak dapat dihapus karena masih memiliki barang terkait.')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki barang terkait.');
        }

        $kategori->delete();
        Cache::forget('all_categories');

        return redirect()->route('admin.categories')
            ->with('toast_title', 'Penghapusan kategori berhasil')
            ->with('toast_message', 'Kategori berhasil dihapus.')
            ->with('success', 'Penghapusan kategori berhasil');
    }

    // =========================================================================
    //  4. VERIFIKASI PERMOHONAN & PENGEMBALIAN SARANA
    // =========================================================================

    /**
     * Menampilkan halaman verifikasi dengan 2 tab:
     * - Tab 1: Antrean permohonan pinjam baru (status 'menunggu')
     * - Tab 2: Antrean konfirmasi fisik barang kembali (status 'disetujui' dan pengembalian belum diverifikasi)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function verifications(Request $request)
    {
        // Tab 1: Pending Requests (status = menunggu)
        $reqSort = $request->input('req_sort');
        $reqDir = strtolower($request->input('req_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $reqQuery = Peminjaman::menunggu()->with(['siswa', 'barang', 'akun']);

        if ($reqSort === 'siswa') {
            $reqQuery->leftJoin('siswa', 'peminjaman.nis', '=', 'siswa.nis')
                     ->select('peminjaman.*')
                     ->orderBy('siswa.nama', $reqDir);
        } elseif ($reqSort === 'status_peminjam') {
            $reqQuery->leftJoin('akun', 'peminjaman.nis', '=', 'akun.nis')
                     ->select('peminjaman.*')
                     ->orderBy('akun.role', $reqDir);
        } elseif ($reqSort === 'nomor_kontak') {
            $reqQuery->leftJoin('akun', 'peminjaman.nis', '=', 'akun.nis')
                     ->leftJoin('siswa', 'peminjaman.nis', '=', 'siswa.nis')
                     ->select('peminjaman.*')
                     ->orderByRaw('COALESCE(NULLIF(akun.nomor_kontak, ?), siswa.no_hp) ' . $reqDir, ['']);
        } elseif ($reqSort === 'barang') {
            $reqQuery->leftJoin('barang', 'peminjaman.kode_barang', '=', 'barang.kode_barang')
                     ->select('peminjaman.*')
                     ->orderBy('barang.nama_barang', $reqDir);
        } elseif (in_array($reqSort, ['lokasi_penggunaan', 'keterangan_penggunaan', 'tanggal_pinjam'])) {
            $reqQuery->orderBy("peminjaman.{$reqSort}", $reqDir);
        } else {
            // Default: data paling baru di atas
            $reqQuery->orderBy('peminjaman.updated_at', 'desc');
        }

        $reqPerPage = in_array((int)$request->input('req_per_page', $request->input('per_page')), [10, 25, 50, 100]) ? (int)$request->input('req_per_page', $request->input('per_page')) : 10;
        $pendingRequests = $reqQuery->paginate($reqPerPage, ['*'], 'req_page')->withQueryString();

        // Tab 2: Pending Returns (hanya pengembalian yang belum dikonfirmasi / kondisi_barang IS NULL)
        $retSort = $request->input('ret_sort');
        $retDir = strtolower($request->input('ret_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $retQuery = Peminjaman::disetujui()
            ->whereHas('pengembalian', function ($q) {
                $q->whereNull('kondisi_barang');
            })
            ->with(['siswa', 'barang', 'pengembalian', 'akun']);

        if ($retSort === 'siswa') {
            $retQuery->leftJoin('siswa', 'peminjaman.nis', '=', 'siswa.nis')
                     ->select('peminjaman.*')
                     ->orderBy('siswa.nama', $retDir);
        } elseif ($retSort === 'status_peminjam') {
            $retQuery->leftJoin('akun', 'peminjaman.nis', '=', 'akun.nis')
                     ->select('peminjaman.*')
                     ->orderBy('akun.role', $retDir);
        } elseif ($retSort === 'nomor_kontak') {
            $retQuery->leftJoin('akun', 'peminjaman.nis', '=', 'akun.nis')
                     ->leftJoin('siswa', 'peminjaman.nis', '=', 'siswa.nis')
                     ->select('peminjaman.*')
                     ->orderByRaw('COALESCE(NULLIF(akun.nomor_kontak, ?), siswa.no_hp) ' . $retDir, ['']);
        } elseif ($retSort === 'barang') {
            $retQuery->leftJoin('barang', 'peminjaman.kode_barang', '=', 'barang.kode_barang')
                     ->select('peminjaman.*')
                     ->orderBy('barang.nama_barang', $retDir);
        } elseif ($retSort === 'tanggal_kembali') {
            $retQuery->leftJoin('pengembalian', 'peminjaman.kode_pinjam', '=', 'pengembalian.kode_pinjam')
                     ->select('peminjaman.*')
                     ->orderBy('pengembalian.tanggal_kembali', $retDir);
        } elseif ($retSort === 'bukti') {
            $retQuery->leftJoin('pengembalian', 'peminjaman.kode_pinjam', '=', 'pengembalian.kode_pinjam')
                     ->select('peminjaman.*')
                     ->orderBy('pengembalian.bukti_foto_video', $retDir);
        } elseif ($retSort === 'tanggal_pinjam') {
            $retQuery->orderBy('peminjaman.tanggal_pinjam', $retDir);
        } else {
            // Default: data paling baru di atas
            $retQuery->orderBy('peminjaman.updated_at', 'desc');
        }

        $retPerPage = in_array((int)$request->input('ret_per_page', $request->input('per_page')), [10, 25, 50, 100]) ? (int)$request->input('ret_per_page', $request->input('per_page')) : 10;
        $pendingReturns = $retQuery->paginate($retPerPage, ['*'], 'ret_page')->withQueryString();

        $activeLoansPerPage = in_array((int) $request->input('active_per_page', 10), [10, 25, 50, 100])
            ? (int) $request->input('active_per_page', 10)
            : 10;
        $activeSort = $request->input('active_sort');
        $activeDir = strtolower($request->input('active_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $activeQuery = Peminjaman::disetujui()
            ->whereDoesntHave('pengembalian')
            ->with(['siswa', 'akun', 'barang']);

        if ($activeSort === 'siswa') {
            $activeQuery->leftJoin('siswa', 'peminjaman.nis', '=', 'siswa.nis')
                ->select('peminjaman.*')
                ->orderBy('siswa.nama', $activeDir);
        } elseif ($activeSort === 'identitas') {
            $activeQuery->orderBy('peminjaman.nis', $activeDir);
        } elseif ($activeSort === 'nomor_kontak') {
            $activeQuery->leftJoin('akun', 'peminjaman.nis', '=', 'akun.nis')
                ->leftJoin('siswa', 'peminjaman.nis', '=', 'siswa.nis')
                ->select('peminjaman.*')
                ->orderByRaw('COALESCE(NULLIF(akun.nomor_kontak, ?), siswa.no_hp) ' . $activeDir, ['']);
        } elseif ($activeSort === 'tanggal_pinjam') {
            $activeQuery->orderBy('peminjaman.tanggal_pinjam', $activeDir);
        } elseif ($activeSort === 'lama_dipinjam') {
            $activeQuery->orderBy('peminjaman.tanggal_pinjam', $activeDir === 'asc' ? 'desc' : 'asc');
        } elseif ($activeSort === 'barang') {
            $activeQuery->leftJoin('barang', 'peminjaman.kode_barang', '=', 'barang.kode_barang')
                ->select('peminjaman.*')
                ->orderBy('barang.nama_barang', $activeDir);
        } else {
            $activeQuery->orderBy('peminjaman.tanggal_pinjam');
        }

        $activeLoans = $activeQuery
            ->paginate($activeLoansPerPage, ['*'], 'active_page')
            ->withQueryString();

        $activeTab = $request->input('tab', 'requests');

        return view('admin.verifications', compact('pendingRequests', 'pendingReturns', 'activeLoans', 'activeTab'));
    }

    /**
     * Menyetujui permohonan peminjaman sarana.
     * Mengubah status transaksi menjadi 'disetujui'.
     *
     * @param  string  $kode  Kode pinjam
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveRequest($kode)
    {
        try {
            $peminjaman = Peminjaman::menunggu()->with(['siswa', 'barang'])->where('kode_pinjam', $kode)->first();

            if (!$peminjaman) {
                return redirect()->route('admin.verifications')
                    ->with('toast_type', 'error')
                    ->with('toast_title', 'Persetujuan pengajuan gagal')
                    ->with('toast_message', "Pengajuan peminjaman ({$kode}) tidak ditemukan atau sudah diproses. Muat ulang halaman, lalu coba lagi.")
                    ->with('error', 'Persetujuan pengajuan gagal');
            }

            $peminjaman->status_pengajuan = 'disetujui';
            $peminjaman->save();

            // Kirim notifikasi ke siswa peminjam
            try {
                NotifikasiService::pengajuanDisetujui($peminjaman);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal mengirim notifikasi persetujuan: " . $e->getMessage());
            }

            $namaPeminjam = $peminjaman->siswa->nama ?? 'Siswa';
            $namaBarang = $peminjaman->barang->nama_barang ?? 'Barang';

            return redirect()->route('admin.verifications')
                ->with('toast_type', 'success')
                ->with('toast_title', 'Persetujuan pengajuan berhasil')
                ->with('toast_message', "Pengajuan peminjaman {$namaBarang} untuk {$namaPeminjam} ({$kode}) telah disetujui.")
                ->with('success', 'Persetujuan pengajuan berhasil');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal menyetujui pengajuan peminjaman.', ['kode_pinjam' => $kode, 'exception' => $e]);
            return redirect()->route('admin.verifications')
                ->with('toast_type', 'error')
                ->with('toast_title', 'Persetujuan pengajuan gagal')
                ->with('toast_message', 'Pengajuan belum dapat disetujui. Coba lagi beberapa saat lagi.')
                ->with('error', 'Persetujuan pengajuan gagal');
        }
    }

    /**
     * Menolak permohonan peminjaman sarana dengan menyertakan alasan penolakan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $kode  Kode pinjam
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejectRequest(Request $request, $kode)
    {
        try {
            $request->validate([
                'alasan_penolakan' => 'nullable|string|max:1000',
            ]);

            $peminjaman = Peminjaman::menunggu()->with(['siswa', 'barang'])->where('kode_pinjam', $kode)->first();

            if (!$peminjaman) {
                return redirect()->route('admin.verifications')
                    ->with('toast_type', 'error')
                    ->with('toast_title', 'Penolakan pengajuan gagal')
                    ->with('toast_message', "Pengajuan peminjaman ({$kode}) tidak ditemukan atau sudah diproses. Muat ulang halaman, lalu coba lagi.")
                    ->with('error', 'Penolakan pengajuan gagal');
            }

            $peminjaman->status_pengajuan = 'ditolak';
            $peminjaman->alasan_penolakan = $request->input('alasan_penolakan');
            $peminjaman->save();

            // Kirim notifikasi penolakan ke siswa peminjam
            try {
                NotifikasiService::pengajuanDitolak($peminjaman, $request->input('alasan_penolakan'));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal mengirim notifikasi penolakan: " . $e->getMessage());
            }

            $namaPeminjam = $peminjaman->siswa->nama ?? 'Siswa';

            return redirect()->route('admin.verifications')
                ->with('toast_type', 'success')
                ->with('toast_title', 'Penolakan pengajuan berhasil')
                ->with('toast_message', "Pengajuan peminjaman ({$kode}) untuk {$namaPeminjam} telah ditolak.")
                ->with('success', 'Penolakan pengajuan berhasil');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal menolak pengajuan peminjaman.', ['kode_pinjam' => $kode, 'exception' => $e]);
            return redirect()->route('admin.verifications')
                ->with('toast_type', 'error')
                ->with('toast_title', 'Penolakan pengajuan gagal')
                ->with('toast_message', 'Pengajuan belum dapat ditolak. Coba lagi beberapa saat lagi.')
                ->with('error', 'Penolakan pengajuan gagal');
        }
    }

    /**
     * Mengonfirmasi pengembalian barang fisik oleh siswa.
     * Menyimpan kondisi fisik hasil serah terima (Baik, Kurang Baik, atau Rusak Berat)
     * dan otomatis mengembalikan unit stok ke kategori kondisi yang bersangkutan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $kode  Kode pinjam
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirmReturn(Request $request, $kode)
    {
        $request->validate([
            'kondisi_barang' => 'required|in:Baik,Kurang Baik,Rusak Berat',
        ]);

        $peminjaman = Peminjaman::disetujui()
            ->where('kode_pinjam', $kode)
            ->with(['pengembalian', 'barang'])
            ->firstOrFail();

        $pengembalian = $peminjaman->pengembalian;

        if (!$pengembalian) {
            return back()
                ->with('toast_title', 'Konfirmasi pengembalian barang gagal')
                ->with('toast_message', 'Data pengembalian tidak ditemukan.')
                ->with('error', 'Data pengembalian tidak ditemukan.');
        }

        // Update kondisi barang pada record pengembalian
        // (Stok barang pada tabel barang otomatis diperbarui oleh trigger database: trg_kembalikan_stok_barang)
        $pengembalian->kondisi_barang = $request->kondisi_barang;
        $pengembalian->save();

        // Kirim notifikasi pengembalian telah diverifikasi ke siswa peminjam
        try {
            NotifikasiService::pengembalianDikonfirmasi($peminjaman, $request->kondisi_barang);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal mengirim notifikasi pengembalian dikonfirmasi: " . $e->getMessage());
        }

        $namaBarang = $peminjaman->barang->nama_barang ?? 'Barang';

        return redirect()->route('admin.verifications', ['tab' => 'returns'])
            ->with('toast_title', 'Konfirmasi pengembalian barang berhasil')
            ->with('toast_message', "Pengembalian {$namaBarang} ({$kode}) telah dikonfirmasi dengan kondisi {$request->kondisi_barang}.")
            ->with('success', 'Konfirmasi pengembalian barang berhasil');
    }
}
