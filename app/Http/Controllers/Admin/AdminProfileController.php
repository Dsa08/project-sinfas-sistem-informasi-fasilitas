<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Controller AdminProfileController
 *
 * Mengelola halaman Pengaturan Akun khusus Admin Sarana SINFAS.
 * Terpisah sepenuhnya dari ProfileController milik Siswa untuk mencegah
 * navigasi yang salah (cross-role confusion).
 *
 * Fitur:
 * 1. Tampilkan halaman pengaturan akun admin dengan tab sidebar.
 * 2. Perbarui profil admin (nama, nomor kontak, email).
 * 3. Ganti kata sandi mandiri dengan verifikasi password lama.
 * 4. Upload/hapus foto profil admin.
 * 5. Kelola preferensi notifikasi admin.
 */
class AdminProfileController extends Controller
{
    /**
     * Menampilkan halaman Pengaturan Akun Admin Sarana dengan tab navigasi aktif.
     *
     * @param string $tab Nama tab pengaturan yang dibuka: 'profile', 'password', 'notifications', atau 'verification'. Default 'profile'.
     * @return \Illuminate\View\View Tampilan admin.profile dengan data akun admin dan penanda tab aktif.
     */
    public function show($tab = 'profile')
    {
        $allowedTabs = ['profile', 'password', 'notifications', 'verification'];
        if (!in_array($tab, $allowedTabs)) {
            $tab = 'profile';
        }

        $user = Auth::user();

        return view('admin.profile', [
            'user'       => $user,
            'activeTab'  => $tab,
        ]);
    }

    /**
     * Memperbarui informasi profil Admin Sarana (nama lengkap, kontak telepon, dan email).
     *
     * Efek Samping:
     * - Memperbarui rekaman pada tabel `akun`.
     * - Menyinkronkan perubahan nama dan kontak pada tabel referensi `staff_sarana`.
     *
     * @param Request $request Permintaan HTTP berisi input 'nama', 'nomor_kontak', dan opsional 'email'.
     * @return \Illuminate\Http\RedirectResponse Pengalihan kembali ke tab profil dengan notifikasi keberhasilan.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nama'         => 'required|string|max:255',
            'nomor_kontak' => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255|unique:akun,email,' . $user->id_akun . ',id_akun',
        ], [
            'nama.required'  => 'Nama lengkap wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah digunakan oleh akun lain.',
        ]);

        $user->nama         = $request->nama;
        $user->nomor_kontak = $request->nomor_kontak;
        if ($request->filled('email')) {
            $user->email = $request->email;
        }
        $user->save();

        if ($user->isAdminSarana() && $user->staffSarana) {
            $user->staffSarana->update([
                'nama' => $user->nama,
                'no_hp' => $user->nomor_kontak,
            ]);
        }

        return redirect()
            ->route('admin.profile', ['tab' => 'profile'])
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Memperbarui kata sandi akun Admin Sarana dengan verifikasi kata sandi lama.
     *
     * Prasyarat:
     * - Kata sandi saat ini (`current_password`) harus cocok dengan hash password pengguna yang terautentikasi.
     * - Kata sandi baru minimal 8 karakter dan mengandung kombinasi huruf serta angka.
     *
     * Efek Samping:
     * - Mengenkripsi (hash) kata sandi baru dan memperbarui kolom `password` pada tabel `akun`.
     *
     * @param Request $request Permintaan HTTP berisi 'current_password', 'new_password', dan 'new_password_confirmation'.
     * @return \Illuminate\Http\RedirectResponse Pengalihan kembali ke tab password atau form semula jika validasi gagal.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'          => 'required|string',
            'new_password'              => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
            'new_password_confirmation' => 'required|string',
        ], [
            'current_password.required'  => 'Password saat ini wajib diisi.',
            'new_password.required'      => 'Password baru wajib diisi.',
            'new_password.confirmed'     => 'Konfirmasi password baru tidak cocok.',
            'new_password.min'           => 'Password baru minimal 8 karakter.',
        ]);

        $user = Auth::user();

        // Verifikasi kebenaran password saat ini
        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])
                ->withInput();
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()
            ->route('admin.profile', ['tab' => 'password'])
            ->with('success', 'Kata sandi berhasil diperbarui.');
    }

    /**
     * Mengunggah dan memperbarui foto profil Admin Sarana.
     *
     * Efek Samping:
     * - Mengonversi gambar ke format WebP terkompresi dan menyimpannya pada storage disk public.
     * - Menghapus file foto avatar lama dari server jika sebelumnya ada.
     * - Memperbarui path foto pada tabel `akun`.
     *
     * @param Request $request Permintaan HTTP berisi berkas gambar 'foto' (maksimal 2MB).
     * @return \Illuminate\Http\RedirectResponse Pengalihan kembali ke tab profil dengan konfirmasi berhasil.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'foto.required' => 'Silakan pilih berkas foto terlebih dahulu.',
            'foto.image'    => 'Berkas harus berupa gambar valid.',
            'foto.mimes'    => 'Format gambar harus berupa jpeg, png, jpg, atau webp.',
            'foto.max'      => 'Ukuran foto profil maksimal 2MB.',
        ]);

        $user = Auth::user();

        $path = app(\App\Services\WebpImageOptimizer::class)
            ->storeOnDisk($request->file('foto'), 'avatars', 'public_uploads', 'avatar');

        app(\App\Services\PublicUploadStorage::class)->delete($user->foto);

        $user->foto = $path;
        $user->save();

        return redirect()
            ->route('admin.profile', ['tab' => 'profile'])
            ->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Menghapus berkas foto profil admin dan mengembalikan tampilan ke inisial/avatar bawaan.
     *
     * Efek Samping:
     * - Menghapus berkas fisik foto dari direktori penyimpanan.
     * - Mengubah kolom `foto` pada basis data menjadi `null`.
     *
     * @return \Illuminate\Http\RedirectResponse Pengalihan kembali ke tab profil dengan pesan sukses.
     */
    public function deletePhoto()
    {
        $user = Auth::user();

        app(\App\Services\PublicUploadStorage::class)->delete($user->foto);

        $user->foto = null;
        $user->save();

        return redirect()
            ->route('admin.profile', ['tab' => 'profile'])
            ->with('success', 'Foto profil berhasil dihapus.');
    }
}
