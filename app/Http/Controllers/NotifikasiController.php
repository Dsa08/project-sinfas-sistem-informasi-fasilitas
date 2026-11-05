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
     * Menampilkan halaman daftar notifikasi lengkap untuk Siswa / Peminjam.
     *
     * Prasyarat:
     * - Pengguna telah terautentikasi.
     * - Jika pengguna adalah Admin Sarana, dialihkan otomatis ke rute antarmuka notifikasi admin.
     *
     * @param Request $request Permintaan HTTP dengan parameter opsi filter status baca ('unread', 'read').
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Tampilan user.notifications atau pengalihan ke antarmuka admin.
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
     * Menampilkan antarmuka pusat notifikasi khusus peran Admin Sarana.
     *
     * Efek Samping:
     * - Seluruh notifikasi yang ditujukan kepada akun admin ini otomatis ditandai sebagai telah dibaca saat halaman dibuka.
     *
     * @param Request $request Permintaan HTTP dengan opsi filter tab.
     * @return \Illuminate\View\View Tampilan admin.notifications berisi daftar notifikasi admin yang dipaginasi.
     */
    public function adminIndex(Request $request)
    {
        $user = Auth::user();

        // Membuka pusat notifikasi dianggap sebagai tanda bahwa admin sudah melihatnya.
        Notifikasi::tandaiSemuaDibaca($user->id_akun);

        $query = Notifikasi::where('id_akun', $user->id_akun)
            ->with(['peminjaman.akun', 'peminjaman.siswa', 'peminjaman.barang', 'peminjaman.pengembalian'])
            ->terbaru();

        $filter = $request->input('filter');
        if ($filter === 'unread') {
            $query->belumDibaca();
        } elseif ($filter === 'read') {
            $query->dibaca();
        }

        $notifikasi = $query->paginate(10)->withQueryString();

        return view('admin.notifications', compact('notifikasi', 'filter'));
    }

    /**
     * Mengambil jumlah notifikasi belum dibaca serta daftar cuplikan notifikasi terbaru untuk dropdown navbar.
     *
     * @return \Illuminate\Http\JsonResponse Respon JSON berisi 'unread_count' dan 'recent' (daftar 5 notifikasi terakhir).
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
                    'created_at_iso' => $item->created_at?->copy()->utc()->toIso8601String(),
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'recent'       => $recent,
        ]);
    }

    /**
     * Menandai satu item notifikasi milik pengguna yang sedang login sebagai sudah dibaca.
     *
     * Efek Samping:
     * - Mengubah kolom `status_baca` menjadi 1 dan merekam timestamp `dibaca_pada`.
     *
     * @param int|string $id Kunci utama (ID) notifikasi yang ditandai dibaca.
     * @param Request $request Permintaan HTTP untuk membedakan respon AJAX/JSON dengan permintaan web biasa.
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Respon JSON sukses atau pengalihan kembali ke halaman sebelumnya.
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
     * Menandai seluruh notifikasi milik pengguna yang sedang login sebagai sudah dibaca.
     *
     * Efek Samping:
     * - Memperbarui seluruh baris belum dibaca milik pengguna menjadi berstatus dibaca.
     *
     * @param Request $request Permintaan HTTP (AJAX/JSON atau form submission).
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Respon status sukses via JSON atau redirect back.
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
