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
    /**
     * Menampilkan formulir wajib ganti kata sandi setelah admin menerbitkan sandi sementara.
     *
     * @return View Tampilan view 'auth.change-required-password'.
     */
    public function showRequiredPasswordChange()
    {
        return view('auth.change-required-password');
    }

    /**
     * Memproses pergantian kata sandi wajib dan membuka kembali hak akses penuh pengguna ke aplikasi.
     *
     * Prasyarat:
     * - Kata sandi saat ini cocok dengan hash database.
     * - Kata sandi baru minimal 8 karakter dan kombinasi huruf serta angka.
     *
     * Efek Samping:
     * - Mengubah kolom `password` dan menyetel `must_change_password = false` pada akun pengguna.
     *
     * @param Request $request Request HTTP yang memuat sandi saat ini, sandi baru, dan konfirmasi sandi.
     * @return RedirectResponse Pengalihan ke beranda peran pengguna dengan pesan sukses.
     */
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
     * Menampilkan antarmuka profil dan pengaturan biodata pengguna yang sedang login.
     *
     * @return View Tampilan view 'user.profile' dengan data pengguna dan alamat email terdaftar.
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
     * Memperbarui informasi nama lengkap, nomor kontak, dan email profil pengguna.
     * Menyinkronkan perubahan ke tabel master yang terkait (`siswa` atau `pegawai`).
     *
     * Efek Samping:
     * - Memperbarui kolom `nama`, `nomor_kontak`, dan `email` pada tabel `akun` dan tabel profil induk.
     *
     * @param Request $request Request HTTP yang memuat input nama lengkap, nomor kontak, dan email.
     * @return RedirectResponse Pengalihan kembali ke halaman profil dengan flash message sukses.
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
     * Memproses penggantian kata sandi mandiri pengguna dari menu profil.
     *
     * Prasyarat:
     * - Kata sandi saat ini harus cocok dengan hash yang tersimpan di basis data.
     *
     * Efek Samping:
     * - Mengubah hash password pengguna di tabel `akun`.
     *
     * @param Request $request Request HTTP yang memuat kata sandi saat ini, sandi baru, dan konfirmasi sandi.
     * @return RedirectResponse Pengalihan kembali ke halaman profil dengan pesan sukses atau error.
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
     * Memproses pengunggahan dan pembaruan foto profil avatar pengguna dengan konversi WebP.
     *
     * Efek Samping:
     * - Mengompresi dan menyimpan berkas foto profil baru ke folder `uploads/avatars/`.
     * - Menghapus foto profil lama dari disk penyimpanan server.
     * - Memperbarui path foto di tabel `akun`.
     *
     * @param Request $request Request HTTP yang memuat berkas foto avatar.
     * @return RedirectResponse Pengalihan kembali ke halaman profil dengan pesan sukses.
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
     * Menghapus foto profil pengguna dan mengembalikannya ke avatar visual inisial bawaan sistem.
     *
     * Efek Samping:
     * - Menghapus berkas foto fisik dari disk server.
     * - Menyetel kolom `foto` menjadi null pada tabel `akun`.
     *
     * @return RedirectResponse Pengalihan kembali ke halaman profil dengan pesan sukses.
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
