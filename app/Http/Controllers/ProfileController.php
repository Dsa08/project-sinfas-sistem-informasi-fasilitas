<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

/**
 * Controller ProfileController
 * 
 * Mengelola informasi profil pribadi pengguna yang sedang aktif (semua role):
 * 1. Menampilkan rincian identitas akun, role, kontak, dan email.
 * 2. Memperbarui informasi personal (nama, nomor kontak, serta sinkronisasi email siswa).
 * 3. Mengubah kata sandi mandiri dengan validasi kecocokan password saat ini dan password rules.
 */
class ProfileController extends Controller
{
    /**
     * Menampilkan halaman pengaturan dan biodata profil pengguna.
     * Mengambil email dari akun atau data siswa terkait.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $user = Auth::user();

        // 1. Ambil email: prioritaskan relasi siswa jika akun role siswa
        $email = $user->email;
        if ($user->isSiswa() && $user->siswa && $user->siswa->email) {
            $email = $user->siswa->email;
        }

        return view('user.profile', [
            'user'  => $user,
            'email' => $email,
        ]);
    }

    /**
     * Memperbarui informasi profil pengguna.
     * Menyinkronkan perubahan nama dan kontak pada tabel 'akun' dan tabel 'siswa'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. Definisikan aturan validasi dasar
        $rules = [
            'full_name'      => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
        ];

        $messages = [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'full_name.max'      => 'Nama lengkap maksimal 255 karakter.',
            'contact_number.max' => 'Nomor kontak maksimal 20 karakter.',
        ];

        // 2. Tambahkan aturan validasi format email jika pengguna adalah siswa
        if ($user->isSiswa()) {
            $rules['email'] = 'nullable|string|email|max:255';
            $messages['email.email'] = 'Format email tidak valid.';
        }

        $request->validate($rules, $messages);

        // 3. Simpan perubahan ke tabel akun
        $user->nama = $request->full_name;
        $user->nomor_kontak = $request->contact_number;
        if ($request->filled('email')) {
            $user->email = $request->email;
        }
        $user->save();

        // 4. Sinkronisasi email ke data master siswa jika relevan
        if ($user->isSiswa() && $user->siswa) {
            $user->siswa->nama = $request->full_name;
            $user->siswa->email = $request->email;
            $user->siswa->no_hp = $request->contact_number;
            $user->siswa->save();
        }

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Memproses penggantian kata sandi mandiri pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        // 1. Validasi input password saat ini dan password baru dengan konfirmasi
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required'     => 'Password baru wajib diisi.',
            'new_password.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = Auth::user();

        // 2. Verifikasi kebenaran password saat ini dengan hash database
        if (!Hash::check($request->current_password, $user->password)) {
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

        // Hapus foto lama dari penyimpanan disk publik jika ada
        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }

        // Simpan foto baru ke direktori 'avatars' pada disk public
        $path = $request->file('foto')->store('avatars', 'public');
        $user->foto = $path;
        $user->save();

        return redirect()->route('profile')->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Menghapus foto profil pengguna dan mengembalikannya ke avatar default.
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

        return redirect()->route('profile')->with('success', 'Foto profil berhasil dihapus.');
    }
}
