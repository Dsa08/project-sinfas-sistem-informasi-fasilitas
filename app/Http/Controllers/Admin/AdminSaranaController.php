<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Controller AdminSaranaController
 * 
 * Mengelola seluruh operasional inventaris sarana dan prasarana sekolah:
 * 1. Dashboard Analitik: Metrik sarana, antrean verifikasi, dan visualisasi agregasi Chart.js 6 bulan terakhir.
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
    public function dashboard()
    {
        // 1. Ringkasan Kartu Statistik (Metric Cards)
        $pendingCount = Peminjaman::menunggu()->count();
        $totalItems = Barang::count();
        $borrowedCount = Peminjaman::disetujui()
            ->whereDoesntHave('pengembalian')
            ->count();
        $damagedCount = Barang::sum('jumlah_rusak_berat');

        // 2. Daftar 5 permohonan pinjam terbaru yang masih berstatus menunggu verifikasi (berdasarkan pembaruan/penambahan)
        $pendingLoans = Peminjaman::menunggu()
            ->with(['siswa', 'barang'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // 3. Mengambil Top 6 barang yang paling sering dipinjam sepanjang masa
        $topLoanItems = Barang::withCount('peminjaman')
            ->orderByDesc('peminjaman_count')
            ->take(6)
            ->get();

        // 4. Membangun data time-series dinamis untuk 6 bulan terakhir
        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $months[] = [
                'year'  => $d->year,
                'month' => $d->month,
                'label' => $namaBulan[$d->month] ?? $d->format('M'),
            ];
        }

        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // 5. Agregasi data peminjaman per item per bulan dari database
        $loanCounts = Peminjaman::select(
                'kode_barang',
                DB::raw('YEAR(tanggal_pinjam) as yr'),
                DB::raw('MONTH(tanggal_pinjam) as mo'),
                DB::raw('COUNT(*) as total')
            )
            ->whereIn('kode_barang', $topLoanItems->pluck('kode_barang'))
            ->whereBetween('tanggal_pinjam', [$startDate, $endDate])
            ->groupBy('kode_barang', 'yr', 'mo')
            ->get()
            ->groupBy('kode_barang');

        // 6. Palet warna kurva batang chart
        $colors = ['#fb7185', '#38bdf8', '#fbbf24', '#60a5fa', '#4ade80', '#a855f7'];
        $chartDatasets = [];

        foreach ($topLoanItems as $index => $item) {
            $itemLoans = $loanCounts->get($item->kode_barang, collect());
            $monthlyCounts = [];
            foreach ($months as $m) {
                $matched = $itemLoans->first(fn($row) => $row->yr == $m['year'] && $row->mo == $m['month']);
                $monthlyCounts[] = $matched ? (int) $matched->total : 0;
            }

            $chartDatasets[] = [
                'label'              => $item->nama_barang,
                'data'               => $monthlyCounts,
                'backgroundColor'    => $colors[$index % count($colors)],
                'borderRadius'       => 4,
                'barPercentage'      => 0.82,
                'categoryPercentage' => 0.8,
            ];
        }

        $chartLabels = array_column($months, 'label');

        return view('admin.dashboard', compact(
            'pendingCount',
            'totalItems',
            'borrowedCount',
            'damagedCount',
            'pendingLoans',
            'topLoanItems',
            'chartLabels',
            'chartDatasets'
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

        $items = $query->paginate(10)->withQueryString();
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
            'tahun_pembelian'    => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
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
        ]);

        $data = $request->except(['foto']);

        // Penanganan upload berkas gambar sarana
        if ($request->hasFile('foto')) {
            $destinationPath = public_path('uploads/items');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $data['foto'] = 'uploads/items/' . $filename;
        }

        Barang::create($data);

        return redirect()->route('admin.items')
            ->with('toast_title', 'Penambahan data barang berhasil')
            ->with('toast_message', "Data barang {$data['nama_barang']} berhasil ditambahkan.")
            ->with('success', 'Penambahan data barang berhasil');
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
            'foto'               => $item->foto ? asset($item->foto) : null,
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
            'tahun_pembelian'    => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
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
        ]);

        $data = $request->except(['kode_barang', 'foto']);

        // Mengganti berkas gambar jika pengguna mengunggah berkas baru
        if ($request->hasFile('foto')) {
            $destinationPath = public_path('uploads/items');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            // Hapus foto lama jika berkas fisik masih ada di storage
            if ($item->foto && file_exists(public_path($item->foto))) {
                @unlink(public_path($item->foto));
            }
            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $data['foto'] = 'uploads/items/' . $filename;
        }

        $item->update($data);

        return redirect()->route('admin.items')
            ->with('toast_title', 'Perubahan data barang berhasil')
            ->with('toast_message', "Data barang {$item->nama_barang} berhasil diperbarui.")
            ->with('success', 'Perubahan data barang berhasil');
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
        if ($item->foto && file_exists(public_path($item->foto))) {
            @unlink(public_path($item->foto));
        }

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

        $categories = $query->paginate(10)->withQueryString();

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
        $reqQuery = Peminjaman::menunggu()->with(['siswa', 'barang']);

        if ($reqSort === 'siswa') {
            $reqQuery->leftJoin('siswa', 'peminjaman.nis', '=', 'siswa.nis')
                     ->select('peminjaman.*')
                     ->orderBy('siswa.nama', $reqDir);
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

        $pendingRequests = $reqQuery->paginate(10, ['*'], 'req_page')->withQueryString();

        // Tab 2: Pending Returns (hanya pengembalian yang belum dikonfirmasi / kondisi_barang IS NULL)
        $retSort = $request->input('ret_sort');
        $retDir = strtolower($request->input('ret_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $retQuery = Peminjaman::disetujui()
            ->whereHas('pengembalian', function ($q) {
                $q->whereNull('kondisi_barang');
            })
            ->with(['siswa', 'barang', 'pengembalian']);

        if ($retSort === 'siswa') {
            $retQuery->leftJoin('siswa', 'peminjaman.nis', '=', 'siswa.nis')
                     ->select('peminjaman.*')
                     ->orderBy('siswa.nama', $retDir);
        } elseif ($retSort === 'barang') {
            $retQuery->leftJoin('barang', 'peminjaman.kode_barang', '=', 'barang.kode_barang')
                     ->select('peminjaman.*')
                     ->orderBy('barang.nama_barang', $retDir);
        } elseif ($retSort === 'tanggal_kembali') {
            $retQuery->leftJoin('pengembalian', 'peminjaman.kode_pinjam', '=', 'pengembalian.kode_pinjam')
                     ->select('peminjaman.*')
                     ->orderBy('pengembalian.tanggal_kembali', $retDir);
        } elseif ($retSort === 'tanggal_pinjam') {
            $retQuery->orderBy('peminjaman.tanggal_pinjam', $retDir);
        } else {
            // Default: data paling baru di atas
            $retQuery->orderBy('peminjaman.updated_at', 'desc');
        }

        $pendingReturns = $retQuery->paginate(10, ['*'], 'ret_page')->withQueryString();

        $activeTab = $request->input('tab', 'requests');

        return view('admin.verifications', compact('pendingRequests', 'pendingReturns', 'activeTab'));
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
        $peminjaman = Peminjaman::menunggu()->with(['siswa', 'barang'])->where('kode_pinjam', $kode)->firstOrFail();
        $peminjaman->status_pengajuan = 'disetujui';
        $peminjaman->save();

        $namaPeminjam = $peminjaman->siswa->nama ?? 'Siswa';
        $namaBarang = $peminjaman->barang->nama_barang ?? 'Barang';

        return redirect()->route('admin.verifications')
            ->with('toast_title', 'Persetujuan pengajuan berhasil')
            ->with('toast_message', "Pengajuan peminjaman {$namaBarang} untuk {$namaPeminjam} ({$kode}) telah disetujui.")
            ->with('success', 'Persetujuan pengajuan berhasil');
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
        $request->validate([
            'alasan_penolakan' => 'nullable|string|max:1000',
        ]);

        $peminjaman = Peminjaman::menunggu()->with(['siswa', 'barang'])->where('kode_pinjam', $kode)->firstOrFail();
        $peminjaman->status_pengajuan = 'ditolak';
        $peminjaman->alasan_penolakan = $request->input('alasan_penolakan');
        $peminjaman->save();

        $namaPeminjam = $peminjaman->siswa->nama ?? 'Siswa';

        return redirect()->route('admin.verifications')
            ->with('toast_title', 'Penolakan pengajuan berhasil')
            ->with('toast_message', "Pengajuan peminjaman ({$kode}) untuk {$namaPeminjam} telah ditolak.")
            ->with('success', 'Penolakan pengajuan berhasil');
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

        $namaBarang = $peminjaman->barang->nama_barang ?? 'Barang';

        return redirect()->route('admin.verifications', ['tab' => 'returns'])
            ->with('toast_title', 'Konfirmasi pengembalian barang berhasil')
            ->with('toast_message', "Pengembalian {$namaBarang} ({$kode}) telah dikonfirmasi dengan kondisi {$request->kondisi_barang}.")
            ->with('success', 'Konfirmasi pengembalian barang berhasil');
    }
}
