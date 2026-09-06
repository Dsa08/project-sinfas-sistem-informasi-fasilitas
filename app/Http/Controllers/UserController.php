<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $categories = Kategori::orderBy('nama_kategori')->get();

        return view('user.dashboard', compact('items', 'categories'));
    }

    /**
     * Status Pengajuan — menampilkan daftar peminjaman milik user yang login.
     */
    public function loanStatus()
    {
        $user = auth()->user();

        $loans = Peminjaman::where('nis', $user->nis)
            ->with(['barang.kategori'])
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

        return view('user.loan-request', compact('item'));
    }

    /**
     * Proses pengajuan peminjaman.
     */
    public function submitLoanRequest(Request $request, $kode)
    {
        $item = Barang::findOrFail($kode);

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

        // Cek ketersediaan
        if ($item->jumlah_baik <= 0) {
            return back()->with('error', 'Barang tidak tersedia untuk dipinjam saat ini.');
        }

        // Generate kode pinjam unik
        $kodePinjam = 'PNJ-' . date('Y') . '-' . str_pad(
            Peminjaman::whereYear('created_at', date('Y'))->count() + 1,
            3, '0', STR_PAD_LEFT
        );

        $user = auth()->user();

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
}
