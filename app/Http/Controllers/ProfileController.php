<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profil user
     */
    public function show()
    {
        $user = Auth::user();

        // Ambil email dari relasi siswa (jika role siswa)
        $email = null;
        if ($user->isSiswa() && $user->siswa) {
            $email = $user->siswa->email;
        }

        return view('user.profile', [
            'user' => $user,
            'email' => $email,
        ]);
    }

    /**
     * Update data profil (personal information)
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'full_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
        ];

        $messages = [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'full_name.max' => 'Nama lengkap maksimal 255 karakter.',
            'contact_number.max' => 'Nomor kontak maksimal 20 karakter.',
        ];

        // Tambahkan validasi email hanya untuk siswa
        if ($user->isSiswa()) {
            $rules['email'] = 'nullable|string|email|max:255';
            $messages['email.email'] = 'Format email tidak valid.';
        }

        $request->validate($rules, $messages);

        // Update data di tabel akun
        $user->nama = $request->full_name;
        $user->nomor_kontak = $request->contact_number;
        $user->save();

        // Update email di tabel siswa (jika role siswa)
        if ($user->isSiswa() && $user->siswa) {
            $user->siswa->email = $request->email;
            $user->siswa->save();
        }

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = Auth::user();

        // Verifikasi password saat ini
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('profile')->with('success', 'Password berhasil diubah.');
    }
}
