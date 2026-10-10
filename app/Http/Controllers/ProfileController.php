<?php

namespace App\Http\Controllers;

use App\Services\PublicUploadStorage;
use App\Services\WebpImageOptimizer;
use App\Support\RoleHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Controller ProfileController
 *
 * Mengelola informasi profil pribadi pengguna yang sedang aktif (semua role):
 * 1. Menampilkan rincian identitas akun, role, kontak, dan email.
 * 2. Memperbarui identitas dan kontak akun beserta profil terkait.
 * 3. Mengubah kata sandi mandiri dengan validasi kecocokan password saat ini dan password rules.
 */
class ProfileController extends Controller
{
    /** Tampilkan formulir penggantian sandi setelah admin menerbitkan sandi sementara. */
    public function showRequiredPasswordChange()
    {
        return view('auth.change-required-password');
    }

    /** Buka kembali akses aplikasi setelah pengguna menetapkan sandi baru yang valid. */
    public function updateRequiredPassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $account = $request->user();
        $account->password = Hash::make($validated['password']);
        $account->must_change_password = false;
        $account->save();

        return redirect()->to(RoleHome::url($account))
            ->with('success', 'Kata sandi berhasil diperbarui.');
    }

    /**
     * Menampilkan halaman pengaturan dan biodata profil pengguna.
     * Mengambil email dari akun atau data siswa terkait.
     *
     * @return View
     */
    public function show()
    {
        $user = Auth::user();

        // Email akun menjadi sumber utama; profil terkait menjadi cadangan.
        $email = $user->email;
        $profile = $user->siswa ?? $user->pegawai;
        if (! $email && $user->isPeminjam() && $profile?->email) {
            $email = $profile->email;
        }

        return view('user.profile', [
            'user' => $user,
            'email' => $email,
        ]);
    }

    /**
     * Memperbarui informasi profil pengguna.
     * Menyinkronkan perubahan nama dan kontak pada tabel 'akun' dan tabel 'siswa'.
     *
     * @return RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. Definisikan aturan validasi dasar
        $rules = [
            'full_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
        ];

        $messages = [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'full_name.max' => 'Nama lengkap maksimal 255 karakter.',
            'contact_number.max' => 'Nomor kontak maksimal 20 karakter.',
        ];

        // 2. Tambahkan aturan validasi format email jika pengguna adalah siswa
        if ($user->isPeminjam()) {
            $rules['email'] = 'nullable|string|email|max:255';
            $messages['email.email'] = 'Format email tidak valid.';
        }

        $request->validate($rules, $messages);

        // 3. Simpan perubahan ke tabel akun
        $user->nama = $request->full_name;
        $user->nomor_kontak = $request->contact_number;
        if ($user->isPeminjam()) {
            $user->email = $request->input('email');
        }
        $user->save();

        // Sinkronkan profil master yang terkait jika tersedia.
        $profile = $user->siswa ?? $user->pegawai;
        if ($user->isPeminjam() && $profile) {
            $profile->nama = $request->full_name;
            $profile->email = $request->email;
            $profile->no_hp = $request->contact_number;
            $profile->save();
        }

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Memproses penggantian kata sandi mandiri pengguna.
     *
     * @return RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        // 1. Validasi input password saat ini dan password baru dengan konfirmasi
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = Auth::user();

        // 2. Verifikasi kebenaran password saat ini dengan hash database
        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        // 3. Simpan hash password baru yang telah memenuhi kriteria keamanan
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('profile')->with('success', 'Password berhasil diubah.');
    }

    /**
     * Memproses pengunggahan dan penggantian foto profil pengguna.
     *
     * @return RedirectResponse
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'foto.required' => 'Silakan pilih berkas foto terlebih dahulu.',
            'foto.image' => 'Berkas harus berupa gambar valid.',
            'foto.mimes' => 'Format gambar harus berupa jpeg, png, jpg, atau webp.',
            'foto.max' => 'Ukuran foto profil maksimal 2MB.',
        ]);

        $user = Auth::user();

        $path = app(WebpImageOptimizer::class)
            ->storeOnDisk($request->file('foto'), 'avatars', 'public_uploads', 'avatar');

        app(PublicUploadStorage::class)->delete($user->foto);

        $user->foto = $path;
        $user->save();

        return redirect()->route('profile')->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Menghapus foto profil pengguna dan mengembalikannya ke avatar default.
     *
     * @return RedirectResponse
     */
    public function deletePhoto()
    {
        $user = Auth::user();

        app(PublicUploadStorage::class)->delete($user->foto);

        $user->foto = null;
        $user->save();

        return redirect()->route('profile')->with('success', 'Foto profil berhasil dihapus.');
    }
}
