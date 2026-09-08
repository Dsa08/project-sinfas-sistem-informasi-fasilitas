<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

/**
 * Controller UserController
 * 
 * Mengelola seluruh alur interaksi dan transaksi operasional untuk pengguna siswa:
 * 1. Dashboard Katalog Barang: Pencarian instan (nama, merk, kode, kategori), filter kategori, dan caching 1 jam.
 * 2. Status & Riwayat Peminjaman: Menampilkan daftar riwayat dan pemantauan status transaksi secara real-time.
 * 3. Permohonan Peminjaman: Detail sarana, kuota pinjam aktif (anti-spam max 2), pencegahan pinjam ganda.
 * 4. Pengembalian Sarana: Formulir pengembalian fisik, upload bukti foto/video (max 10MB), dan inspeksi serah terima.
 */
class UserController extends Controller
{
    /**
     * Menampilkan dashboard utama siswa dengan katalog sarana prasarana.
     * Mengimplementasikan pencarian multifield, filter kategori, paginasi, dan caching kategori.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        // 1. Inisialisasi query dengan eager loading relasi kategori (mencegah N+1 problem)
        $query = Barang::with('kategori');

        // 2. Fitur Pencarian Cerdas: mencari berdasarkan nama barang, merk/model, kode barang, atau nama kategori
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

        // 3. Filter berdasarkan id_kategori tertentu
        if ($kategoriId = $request->input('kategori')) {
            $query->where('id_kategori', $kategoriId);
        }

        // 4. Paginasi hasil (12 item per halaman) dengan mempertahankan query string pada pagination link
        $items = $query->orderBy('nama_barang', 'asc')->paginate(12)->withQueryString();
        
        // 5. Caching daftar kategori selama 3600 detik (1 jam) untuk optimalisasi performa database
        $categories = Cache::remember('all_categories', 3600, function () {
            return Kategori::orderBy('nama_kategori')->get();
        });

        return view('user.dashboard', compact('items', 'categories'));
    }

    /**
     * Menampilkan status transaksi peminjaman milik siswa yang sedang login.
     * Termasuk relasi barang, kategori, dan catatan pengembalian.
     *
     * @return \Illuminate\View\View
     */
    public function loanStatus()
    {
        $user = auth()->user();

        // Mengambil seluruh permohonan pinjam siswa urut dari yang paling baru
        $loans = Peminjaman::where('nis', $user->nis)
            ->with(['barang.kategori', 'pengembalian'])
            ->latest('created_at')
            ->paginate(10);

        return view('user.loan-status', compact('loans'));
    }

    /**
     * Menampilkan halaman detail sarana dan formulir pengajuan peminjaman.
     * Memeriksa kuota pinjam siswa dan status pengajuan yang sedang berjalan.
     *
     * @param  string  $kode  Kode unik barang yang akan dipinjam
     * @return \Illuminate\View\View
     */
    public function loanRequest($kode)
    {
        $item = Barang::with('kategori')->findOrFail($kode);
        $user = auth()->user();

        // 1. Hitung jumlah peminjaman aktif siswa (yang masih menunggu verifikasi atau sedang dipinjam)
        $activeLoansCount = Peminjaman::where('nis', $user->nis)
            ->whereIn('status_pengajuan', ['menunggu', 'disetujui'])
            ->whereDoesntHave('pengembalian', function ($q) {
                $q->whereNotNull('kondisi_barang');
            })
            ->count();

        // 2. Periksa apakah siswa sudah memiliki permohonan pending untuk barang spesifik ini
        $alreadyPending = Peminjaman::where('nis', $user->nis)
            ->where('kode_barang', $kode)
            ->where('status_pengajuan', 'menunggu')
            ->exists();

        return view('user.loan-request', compact('item', 'activeLoansCount', 'alreadyPending'));
    }

    /**
     * Memproses pengiriman formulir permohonan peminjaman dengan aturan validasi dan anti-spam.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $kode  Kode unik barang
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitLoanRequest(Request $request, $kode)
    {
        $item = Barang::findOrFail($kode);
        $user = auth()->user();

        // 1. Aturan Anti-Spam: Batas maksimal kuota peminjaman aktif adalah 2 transaksi per siswa
        $activeLoansCount = Peminjaman::where('nis', $user->nis)
            ->whereIn('status_pengajuan', ['menunggu', 'disetujui'])
            ->whereDoesntHave('pengembalian', function ($q) {
                $q->whereNotNull('kondisi_barang');
            })
            ->count();

        if ($activeLoansCount >= 2) {
            return back()->with('error', 'Anda telah mencapai batas maksimal 2 peminjaman aktif (menunggu / sedang dipinjam). Selesaikan peminjaman sebelumnya terlebih dahulu.');
        }

        // 2. Aturan Anti-Duplikasi: Mencegah permohonan ganda yang masih berstatus menunggu verifikasi
        $alreadyPending = Peminjaman::where('nis', $user->nis)
            ->where('kode_barang', $kode)
            ->where('status_pengajuan', 'menunggu')
            ->exists();

        if ($alreadyPending) {
            return back()->with('error', 'Anda sudah memiliki permohonan untuk alat ini yang masih menunggu verifikasi admin sarana.');
        }

        // 3. Aturan Kepemilikan: Mencegah peminjaman alat yang sama jika unit sebelumnya belum dikembalikan
        $alreadyBorrowing = Peminjaman::where('nis', $user->nis)
            ->where('kode_barang', $kode)
            ->where('status_pengajuan', 'disetujui')
            ->whereDoesntHave('pengembalian')
            ->exists();

        if ($alreadyBorrowing) {
            return back()->with('error', 'Anda saat ini sedang meminjam alat ini. Kembalikan alat terlebih dahulu sebelum meminjam unit baru.');
        }

        // 4. Validasi Form Input Pengajuan
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

        // 5. Verifikasi ketersediaan stok fisik barang dalam kondisi baik
        if ($item->jumlah_baik <= 0) {
            return back()->with('error', 'Barang tidak tersedia untuk dipinjam saat ini.');
        }

        // 6. Generate kode pinjam unik berurutan berdasarkan tahun berjalan (contoh: 'PNJ-2026-001')
        $kodePinjam = 'PNJ-' . date('Y') . '-' . str_pad(
            Peminjaman::whereYear('created_at', date('Y'))->count() + 1,
            3, '0', STR_PAD_LEFT
        );

        // 7. Simpan permohonan baru ke database dengan status awal 'menunggu'
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
            ->with('success', 'Pengajuan peminjaman berhasil dikirim! Kode: ' . $kodePinjam);
    }

    /**
     * Menampilkan halaman pengembalian sarana yang sedang dipinjam oleh siswa.
     *
     * @param  string  $kode  Kode transaksi peminjaman
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function loanReturn($kode)
    {
        $user = auth()->user();

        // Cari transaksi peminjaman milik user yang berstatus 'disetujui'
        $loan = Peminjaman::where('nis', $user->nis)
            ->where('kode_pinjam', $kode)
            ->where('status_pengajuan', 'disetujui')
            ->with(['barang.kategori', 'pengembalian'])
            ->firstOrFail();

        // Mencegah pengajuan pengembalian ulang jika sudah diajukan sebelumnya
        if ($loan->pengembalian) {
            return redirect()->route('loan.status')
                ->with('error', 'Pengembalian untuk peminjaman ini sudah diajukan sebelumnya.');
        }

        return view('user.loan-return', compact('loan'));
    }

    /**
     * Memproses pengajuan pengembalian sarana dari siswa beserta upload bukti fisik (foto/video).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $kode  Kode transaksi peminjaman
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitReturnRequest(Request $request, $kode)
    {
        $user = auth()->user();

        // 1. Verifikasi kepemilikan transaksi pinjam
        $loan = Peminjaman::where('nis', $user->nis)
            ->where('kode_pinjam', $kode)
            ->where('status_pengajuan', 'disetujui')
            ->with('pengembalian')
            ->firstOrFail();

        if ($loan->pengembalian) {
            return redirect()->route('loan.status')
                ->with('error', 'Pengembalian untuk peminjaman ini sudah diajukan sebelumnya.');
        }

        // 2. Validasi input formulir pengembalian dan file bukti fisik
        $request->validate([
            'tanggal_kembali'  => 'required|date',
            'catatan'          => 'nullable|string|max:1000',
            'bukti_foto_video' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4|max:10240',
        ], [
            'tanggal_kembali.required' => 'Tanggal pengembalian wajib diisi.',
            'bukti_foto_video.max'     => 'Ukuran bukti foto/video maksimal 10MB.',
            'bukti_foto_video.mimes'   => 'Format bukti harus berupa JPG, PNG, WEBP, atau MP4.',
        ]);

        // 3. Simpan file bukti ke direktori public/uploads/returns jika diunggah
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

        // 4. Generate kode kembali unik (contoh: 'KMB-2026-001')
        $kodeKembali = 'KMB-' . date('Y') . '-' . str_pad(
            Pengembalian::whereYear('created_at', date('Y'))->count() + 1,
            3, '0', STR_PAD_LEFT
        );

        // 5. Buat catatan pengembalian (kondisi_barang masih null menunggu konfirmasi fisik dari admin sarana)
        Pengembalian::create([
            'kode_kembali'     => $kodeKembali,
            'kode_pinjam'      => $loan->kode_pinjam,
            'tanggal_kembali'  => $request->tanggal_kembali,
            'kondisi_barang'   => null,
            'bukti_foto_video' => $buktiPath,
            'catatan'          => $request->catatan,
        ]);

        return redirect()->route('loan.status')
            ->with('success', 'Pengajuan pengembalian barang berhasil dikirim! Menunggu verifikasi admin sarana.');
    }
}
