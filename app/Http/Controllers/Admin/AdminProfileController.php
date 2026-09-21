<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
     * Menampilkan halaman Pengaturan Akun admin dengan tab yang aktif.
     * Default tab adalah 'profile'.
     *
     * @param  string  $tab  Tab aktif: profile|password|notifications|verification
     * @return \Illuminate\View\View
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
     * Memperbarui data profil Admin Sarana (nama, nomor kontak, email).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
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

        return redirect()
            ->route('admin.profile', ['tab' => 'profile'])
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Memperbarui kata sandi Admin Sarana dengan verifikasi password lama.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
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
     * Memperbarui foto profil Admin Sarana.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
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

        // Hapus foto lama jika ada
        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }

        $path       = $request->file('foto')->store('avatars', 'public');
        $user->foto = $path;
        $user->save();

        return redirect()
            ->route('admin.profile', ['tab' => 'profile'])
            ->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Menghapus foto profil Admin Sarana dan mengembalikan ke avatar default.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletePhoto()
    {
        $user = Auth::user();

        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }

        $user->foto = null;
        $user->save();

        return redirect()
            ->route('admin.profile', ['tab' => 'profile'])
            ->with('success', 'Foto profil berhasil dihapus.');
    }
}
