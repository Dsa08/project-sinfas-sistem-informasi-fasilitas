<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    /**
     * Dashboard Siswa — menampilkan daftar barang yang tersedia.
     */
    public function dashboard(Request $request)
    {
        $query = Barang::with('kategori');

        // Search: nama_barang, merk_model, kategori
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('merk_model', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhereHas('kategori', function ($kq) use ($search) {
                      $kq->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by kategori
        if ($kategoriId = $request->input('kategori')) {
            $query->where('id_kategori', $kategoriId);
        }

        $items = $query->orderBy('nama_barang', 'asc')->paginate(12)->withQueryString();
        
        // Cache kategori untuk mengurangi beban query database
        $categories = Cache::remember('all_categories', 3600, function () {
            return Kategori::orderBy('nama_kategori')->get();
        });

        return view('user.dashboard', compact('items', 'categories'));
    }

    /**
     * Status Pengajuan — menampilkan daftar peminjaman milik user yang login.
     */
    public function loanStatus()
    {
        $user = auth()->user();

        $loans = Peminjaman::where('nis', $user->nis)
            ->with(['barang.kategori', 'pengembalian'])
            ->latest('created_at')
            ->paginate(10);

        return view('user.loan-status', compact('loans'));
    }

    /**
     * Halaman Loan Request — menampilkan detail barang dan form peminjaman.
     */
    public function loanRequest($kode)
    {
        $item = Barang::with('kategori')->findOrFail($kode);
        $user = auth()->user();

        // Cek kuota aktif siswa
        $activeLoansCount = Peminjaman::where('nis', $user->nis)
            ->whereIn('status_pengajuan', ['menunggu', 'disetujui'])
            ->whereDoesntHave('pengembalian', function ($q) {
                $q->whereNotNull('kondisi_barang');
            })
            ->count();

        // Cek apakah user sedang menunggu approval untuk barang ini
        $alreadyPending = Peminjaman::where('nis', $user->nis)
            ->where('kode_barang', $kode)
            ->where('status_pengajuan', 'menunggu')
            ->exists();

        return view('user.loan-request', compact('item', 'activeLoansCount', 'alreadyPending'));
    }

    /**
     * Proses pengajuan peminjaman dengan proteksi anti-spam.
     */
    public function submitLoanRequest(Request $request, $kode)
    {
        $item = Barang::findOrFail($kode);
        $user = auth()->user();

        // 1. Anti-Spam: Batas Kuota Maksimal Peminjaman Aktif (Maks 2)
        $activeLoansCount = Peminjaman::where('nis', $user->nis)
            ->whereIn('status_pengajuan', ['menunggu', 'disetujui'])
            ->whereDoesntHave('pengembalian', function ($q) {
                $q->whereNotNull('kondisi_barang');
            })
            ->count();

        if ($activeLoansCount >= 2) {
            return back()->with('error', 'Anda telah mencapai batas maksimal 2 peminjaman aktif (menunggu / sedang dipinjam). Selesaikan peminjaman sebelumnya terlebih dahulu.');
        }

        // 2. Anti-Duplikasi: Cek apakah sudah ada request menunggu untuk barang ini
        $alreadyPending = Peminjaman::where('nis', $user->nis)
            ->where('kode_barang', $kode)
            ->where('status_pengajuan', 'menunggu')
            ->exists();

        if ($alreadyPending) {
            return back()->with('error', 'Anda sudah memiliki permohonan untuk alat ini yang masih menunggu verifikasi admin sarana.');
        }

        // 3. Cek apakah user sedang meminjam alat ini dan belum dikembalikan
        $alreadyBorrowing = Peminjaman::where('nis', $user->nis)
            ->where('kode_barang', $kode)
            ->where('status_pengajuan', 'disetujui')
            ->whereDoesntHave('pengembalian')
            ->exists();

        if ($alreadyBorrowing) {
            return back()->with('error', 'Anda saat ini sedang meminjam alat ini. Kembalikan alat terlebih dahulu sebelum meminjam unit baru.');
        }

        // 4. Validasi Form Input
        $request->validate([
            'tanggal_pinjam'        => 'required|date|after_or_equal:today',
            'lokasi_penggunaan'     => 'required|string|max:255',
            'keterangan_penggunaan' => 'required|string|max:1000',
        ], [
            'tanggal_pinjam.required'        => 'Tanggal pinjam wajib diisi.',
            'tanggal_pinjam.after_or_equal'  => 'Tanggal pinjam tidak boleh sebelum hari ini.',
            'lokasi_penggunaan.required'     => 'Lokasi penggunaan wajib diisi.',
            'keterangan_penggunaan.required' => 'Alasan/keperluan wajib diisi.',
        ]);

        // 5. Cek ketersediaan fisik barang
        if ($item->jumlah_baik <= 0) {
            return back()->with('error', 'Barang tidak tersedia untuk dipinjam saat ini.');
        }

        // 6. Generate kode pinjam unik aman
        $kodePinjam = 'PNJ-' . date('Y') . '-' . str_pad(
            Peminjaman::whereYear('created_at', date('Y'))->count() + 1,
            3, '0', STR_PAD_LEFT
        );

        Peminjaman::create([
            'kode_pinjam'           => $kodePinjam,
            'nis'                   => $user->nis,
            'kode_barang'           => $kode,
            'tanggal_pinjam'        => $request->tanggal_pinjam,
            'lokasi_penggunaan'     => $request->lokasi_penggunaan,
            'keterangan_penggunaan' => $request->keterangan_penggunaan,
            'status_pengajuan'      => 'menunggu',
        ]);

        return redirect()->route('loan.status')
            ->with('success', 'Pengajuan peminjaman berhasil dikirim! Kode: ' . $kodePinjam)
            ->with('show_waiting_modal', true);
    }

    /**
     * Halaman Return Item — menampilkan detail barang pinjaman dan form pengembalian.
     */
    public function loanReturn($kode)
    {
        $user = auth()->user();

        $loan = Peminjaman::where('nis', $user->nis)
            ->where('kode_pinjam', $kode)
            ->where('status_pengajuan', 'disetujui')
            ->with(['barang.kategori', 'pengembalian'])
            ->firstOrFail();

        // Jika sudah pernah mengajukan pengembalian
        if ($loan->pengembalian) {
            return redirect()->route('loan.status')
                ->with('error', 'Pengembalian untuk peminjaman ini sudah diajukan sebelumnya.');
        }

        return view('user.loan-return', compact('loan'));
    }

    /**
     * Proses pengajuan pengembalian barang dari siswa.
     */
    public function submitReturnRequest(Request $request, $kode)
    {
        $user = auth()->user();

        $loan = Peminjaman::where('nis', $user->nis)
            ->where('kode_pinjam', $kode)
            ->where('status_pengajuan', 'disetujui')
            ->with('pengembalian')
            ->firstOrFail();

        if ($loan->pengembalian) {
            return redirect()->route('loan.status')
                ->with('error', 'Pengembalian untuk peminjaman ini sudah diajukan sebelumnya.');
        }

        $request->validate([
            'tanggal_kembali'  => 'required|date',
            'catatan'          => 'nullable|string|max:1000',
            'bukti_foto_video' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4|max:10240',
        ], [
            'tanggal_kembali.required' => 'Tanggal pengembalian wajib diisi.',
            'bukti_foto_video.max'     => 'Ukuran bukti foto/video maksimal 10MB.',
            'bukti_foto_video.mimes'   => 'Format bukti harus berupa JPG, PNG, WEBP, atau MP4.',
        ]);

        $buktiPath = null;
        if ($request->hasFile('bukti_foto_video')) {
            $file = $request->file('bukti_foto_video');
            $destinationPath = public_path('uploads/returns');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $fileName = 'return_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);
            $buktiPath = 'uploads/returns/' . $fileName;
        }

        $kodeKembali = 'KMB-' . date('Y') . '-' . str_pad(
            Pengembalian::whereYear('created_at', date('Y'))->count() + 1,
            3, '0', STR_PAD_LEFT
        );

        Pengembalian::create([
            'kode_kembali'     => $kodeKembali,
            'kode_pinjam'      => $loan->kode_pinjam,
            'tanggal_kembali'  => $request->tanggal_kembali,
            'kondisi_barang'   => null, // Menunggu verifikasi admin sarana
            'bukti_foto_video' => $buktiPath,
            'catatan'          => $request->catatan,
        ]);

        return redirect()->route('loan.status')
            ->with('success', 'Pengajuan pengembalian barang berhasil dikirim! Menunggu verifikasi admin sarana.');
    }
}
