<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminSaranaController extends Controller
{
    // =============================================
    //  DASHBOARD
    // =============================================

    /**
     * Dashboard Admin Sarana — statistik & pending loan requests.
     */
    public function dashboard()
    {
        // Stat cards
        $pendingCount = Peminjaman::menunggu()->count();
        $totalItems = Barang::count();
        $borrowedCount = Peminjaman::disetujui()
            ->whereDoesntHave('pengembalian')
            ->count();
        $damagedCount = Barang::sum('jumlah_rusak_berat');

        // Pending loan requests (5 terbaru)
        $pendingLoans = Peminjaman::menunggu()
            ->with(['siswa', 'barang'])
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'pendingCount',
            'totalItems',
            'borrowedCount',
            'damagedCount',
            'pendingLoans'
        ));
    }

    // =============================================
    //  KELOLA DATA ALAT (BARANG)
    // =============================================

    /**
     * Menampilkan daftar barang dengan search & pagination.
     */
    public function items(Request $request)
    {
        $query = Barang::with('kategori');

        // Search
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

        $items = $query->orderBy('nama_barang', 'asc')->paginate(10)->withQueryString();
        $categories = Kategori::orderBy('nama_kategori')->get();

        return view('admin.items', compact('items', 'categories'));
    }

    /**
     * Simpan barang baru.
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
        ], [
            'kode_barang.required' => 'Kode barang wajib diisi.',
            'kode_barang.unique'   => 'Kode barang sudah digunakan.',
            'id_kategori.required' => 'Kategori wajib dipilih.',
            'nama_barang.required' => 'Nama barang wajib diisi.',
        ]);

        Barang::create($request->all());

        return redirect()->route('admin.items')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    /**
     * Ambil detail barang (JSON untuk modal edit).
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
        ]);
    }

    /**
     * Update data barang.
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
        ]);

        $item->update($request->except('kode_barang'));

        return redirect()->route('admin.items')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    /**
     * Hapus barang.
     */
    public function destroyItem($kode)
    {
        $item = Barang::findOrFail($kode);

        // Cek apakah ada peminjaman aktif
        $activeLoan = Peminjaman::where('kode_barang', $kode)
            ->whereIn('status_pengajuan', ['menunggu', 'disetujui'])
            ->whereDoesntHave('pengembalian')
            ->exists();

        if ($activeLoan) {
            return back()->with('error', 'Barang tidak dapat dihapus karena masih ada peminjaman aktif.');
        }

        $item->delete();

        return redirect()->route('admin.items')
            ->with('success', 'Barang berhasil dihapus.');
    }

    // =============================================
    //  KELOLA KATEGORI
    // =============================================

    /**
     * Menampilkan daftar kategori dengan jumlah barang & pagination.
     */
    public function categories(Request $request)
    {
        $query = Kategori::withCount('barang');

        if ($search = $request->input('search')) {
            $query->where('nama_kategori', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('nama_kategori', 'asc')->paginate(10)->withQueryString();

        return view('admin.categories', compact('categories'));
    }

    /**
     * Simpan kategori baru.
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

        return redirect()->route('admin.categories')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Update kategori.
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

        return redirect()->route('admin.categories')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori.
     */
    public function destroyCategory($id)
    {
        $kategori = Kategori::withCount('barang')->findOrFail($id);

        if ($kategori->barang_count > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki barang terkait.');
        }

        $kategori->delete();

        return redirect()->route('admin.categories')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    // =============================================
    //  VERIFIKASI PEMINJAMAN & PENGEMBALIAN
    // =============================================

    /**
     * Menampilkan halaman verifikasi: pending requests & pending returns.
     */
    public function verifications(Request $request)
    {
        // Tab 1: Pending Requests (status = menunggu)
        $pendingRequests = Peminjaman::menunggu()
            ->with(['siswa', 'barang'])
            ->latest('created_at')
            ->paginate(10, ['*'], 'req_page')
            ->withQueryString();

        // Tab 2: Pending Returns (status = disetujui, belum ada pengembalian yang dikonfirmasi)
        // Peminjaman disetujui yang sudah ada record pengembalian tapi belum di-confirm
        $pendingReturns = Peminjaman::disetujui()
            ->whereHas('pengembalian')
            ->with(['siswa', 'barang', 'pengembalian'])
            ->latest('pengembalian.created_at')
            ->paginate(10, ['*'], 'ret_page')
            ->withQueryString();

        $activeTab = $request->input('tab', 'requests');

        return view('admin.verifications', compact('pendingRequests', 'pendingReturns', 'activeTab'));
    }

    /**
     * Setujui peminjaman.
     */
    public function approveRequest($kode)
    {
        $peminjaman = Peminjaman::menunggu()->where('kode_pinjam', $kode)->firstOrFail();
        $peminjaman->status_pengajuan = 'disetujui';
        $peminjaman->save();

        return redirect()->route('admin.verifications')
            ->with('success', "Peminjaman {$kode} berhasil disetujui.");
    }

    /**
     * Tolak peminjaman.
     */
    public function rejectRequest($kode)
    {
        $peminjaman = Peminjaman::menunggu()->where('kode_pinjam', $kode)->firstOrFail();
        $peminjaman->status_pengajuan = 'ditolak';
        $peminjaman->save();

        return redirect()->route('admin.verifications')
            ->with('success', "Peminjaman {$kode} berhasil ditolak.");
    }

    /**
     * Konfirmasi pengembalian barang + update kondisi.
     */
    public function confirmReturn(Request $request, $kode)
    {
        $request->validate([
            'kondisi_barang' => 'required|in:Baik,Kurang Baik,Rusak Berat',
        ]);

        $peminjaman = Peminjaman::disetujui()
            ->where('kode_pinjam', $kode)
            ->with('pengembalian')
            ->firstOrFail();

        $pengembalian = $peminjaman->pengembalian;

        if (!$pengembalian) {
            return back()->with('error', 'Data pengembalian tidak ditemukan.');
        }

        // Update kondisi barang pada record pengembalian
        $pengembalian->kondisi_barang = $request->kondisi_barang;
        $pengembalian->save();

        // Update stok barang berdasarkan kondisi yang dipilih
        $barang = $peminjaman->barang;
        if ($barang) {
            match ($request->kondisi_barang) {
                'Baik'         => $barang->increment('jumlah_baik'),
                'Kurang Baik'  => $barang->increment('jumlah_kurang_baik'),
                'Rusak Berat'  => $barang->increment('jumlah_rusak_berat'),
            };
        }

        return redirect()->route('admin.verifications', ['tab' => 'returns'])
            ->with('success', "Pengembalian untuk peminjaman {$kode} berhasil dikonfirmasi.");
    }
}
