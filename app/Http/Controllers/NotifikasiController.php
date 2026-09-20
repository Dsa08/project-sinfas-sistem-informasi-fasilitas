<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller NotifikasiController
 * 
 * Mengelola antarmuka dan interaksi Pusat Notifikasi (In-App Notification Center):
 * - Halaman daftar notifikasi siswa (user/notifications.blade.php)
 * - Halaman daftar notifikasi admin sarana (admin/notifications.blade.php)
 * - Endpoint AJAX hitung notifikasi belum dibaca & preview popover
 * - Tandai notifikasi sudah dibaca (satuan maupun sekaligus)
 */
class NotifikasiController extends Controller
{
    /**
     * Menampilkan halaman daftar notifikasi lengkap untuk Siswa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Jika admin sarana tidak sengaja membuka route /notifications, arahkan ke halaman notifikasi admin
        if ($user && $user->isAdminSarana()) {
            return redirect()->route('admin.notifications');
        }

        $query = Notifikasi::where('id_akun', $user->id_akun)
            ->with(['peminjaman.barang.kategori', 'peminjaman.pengembalian'])
            ->terbaru();

        // Filter tab: semua, belum_dibaca, atau tipe tertentu
        $filter = $request->input('filter');
        if ($filter === 'unread') {
            $query->belumDibaca();
        } elseif ($filter === 'read') {
            $query->dibaca();
        }

        $notifikasi = $query->paginate(15)->withQueryString();
        $unreadCount = Notifikasi::where('id_akun', $user->id_akun)->belumDibaca()->count();

        return view('user.notifications', compact('notifikasi', 'unreadCount', 'filter'));
    }

    /**
     * Menampilkan halaman daftar notifikasi khusus Admin Sarana.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function adminIndex(Request $request)
    {
        $user = Auth::user();

        $query = Notifikasi::where('id_akun', $user->id_akun)
            ->with(['peminjaman.siswa', 'peminjaman.barang', 'peminjaman.pengembalian'])
            ->terbaru();

        $filter = $request->input('filter');
        if ($filter === 'unread') {
            $query->belumDibaca();
        } elseif ($filter === 'read') {
            $query->dibaca();
        }

        $notifikasi = $query->paginate(15)->withQueryString();
        $unreadCount = Notifikasi::where('id_akun', $user->id_akun)->belumDibaca()->count();

        return view('admin.notifications', compact('notifikasi', 'unreadCount', 'filter'));
    }

    /**
     * API JSON: Mengambil jumlah notifikasi belum dibaca dan beberapa item terbaru untuk popover/dropdown navbar.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function hitungBelumDibaca()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread_count' => 0, 'recent' => []]);
        }

        $unreadCount = Notifikasi::where('id_akun', $user->id_akun)->belumDibaca()->count();
        $recent = Notifikasi::where('id_akun', $user->id_akun)
            ->with(['peminjaman.barang'])
            ->terbaru()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id_notifikasi' => $item->id_notifikasi,
                    'tipe'          => $item->tipe,
                    'tipe_label'    => $item->tipe_label,
                    'judul'         => $item->judul,
                    'pesan'         => $item->pesan,
                    'status_baca'   => (bool) $item->status_baca,
                    'kode_pinjam'   => $item->kode_pinjam,
                    'hari_berlalu'  => $item->hari_berlalu,
                    'warna_durasi'  => $item->warna_durasi,
                    'created_at_human' => $item->created_at ? $item->created_at->diffForHumans() : '',
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'recent'       => $recent,
        ]);
    }

    /**
     * Menandai satu notifikasi sebagai sudah dibaca.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tandaiDibaca($id, Request $request)
    {
        $user = Auth::user();

        $notif = Notifikasi::where('id_akun', $user->id_akun)
            ->where('id_notifikasi', $id)
            ->first();

        if ($notif) {
            $notif->tandaiDibaca();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    /**
     * Menandai seluruh notifikasi pengguna sebagai sudah dibaca.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tandaiSemuaDibaca(Request $request)
    {
        $user = Auth::user();

        Notifikasi::tandaiSemuaDibaca($user->id_akun);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}
