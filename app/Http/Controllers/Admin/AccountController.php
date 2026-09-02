<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Siswa;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Menampilkan daftar semua akun dengan search, sort, dan pagination.
     */
    public function index(Request $request)
    {
        $query = Akun::where('role', '!=', 'admin_sistem');

        // Search: nama, username, nis, nip, email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->input('sort', 'nama');
        $sortDir = $request->input('dir', 'asc');
        $allowedSorts = ['nama', 'nis', 'nip', 'role', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('nama', 'asc');
        }

        $accounts = $query->paginate(10)->withQueryString();

        // Statistik ringkas untuk header
        $totalAccounts = Akun::count();
        $totalActive = Akun::active()->count();

        return view('admin.system.accounts', compact('accounts', 'totalAccounts', 'totalActive'));
    }

    /**
     * Simpan akun baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'        => 'required|string|max:255',
            'nis_nip'     => 'required|string|max:20',
            'role'        => 'required|in:siswa,admin_sarana,admin_sistem',
            'nomor_kontak'=> 'nullable|string|max:20',
            'username'    => 'required|string|max:255|unique:akun,username',
            'email'       => 'nullable|email|max:255|unique:akun,email',
            'password'    => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
            'foto'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nama.required'     => 'Nama wajib diisi.',
            'nis_nip.required'  => 'NIS/NIP wajib diisi.',
            'role.required'     => 'Role wajib dipilih.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan.',
            'email.unique'      => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        $nisNip = trim($request->nis_nip);
        $role = $request->role;
        $nis = null;
        $nip = null;

        // Validasi NIS/NIP sesuai role
        if ($role === 'siswa') {
            $siswa = Siswa::where('nis', $nisNip)->first();
            if (!$siswa) {
                return back()->withErrors(['nis_nip' => 'NIS tidak ditemukan di data siswa.'])->withInput();
            }
            if (Akun::where('nis', $nisNip)->exists()) {
                return back()->withErrors(['nis_nip' => 'NIS ini sudah memiliki akun terdaftar.'])->withInput();
            }
            $nis = $nisNip;
        } elseif ($role === 'admin_sarana') {
            $pegawai = Pegawai::where('nip', $nisNip)->first();
            if (!$pegawai) {
                return back()->withErrors(['nis_nip' => 'NIP tidak ditemukan di data pegawai.'])->withInput();
            }
            if (Akun::where('nip', $nisNip)->exists()) {
                return back()->withErrors(['nis_nip' => 'NIP ini sudah memiliki akun terdaftar.'])->withInput();
            }
            $nip = $nisNip;
        }
        // admin_sistem: NIS/NIP opsional, tetap disimpan sebagai referensi

        // Upload foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('avatars', 'public');
        }

        Akun::create([
            'nis'          => $nis,
            'nip'          => $nip,
            'nama'         => $request->nama,
            'nomor_kontak' => $request->nomor_kontak,
            'email'        => $request->email,
            'role'         => $role,
            'username'     => $request->username,
            'password'     => $request->password, // Auto-hashed via $casts
            'foto'         => $fotoPath,
            'is_active'    => true,
        ]);

        return redirect()->route('admin.sistem.accounts')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    /**
     * Ambil data detail akun (JSON untuk modal).
     */
    public function show($id)
    {
        $akun = Akun::findOrFail($id);

        return response()->json([
            'id_akun'      => $akun->id_akun,
            'nama'         => $akun->nama,
            'nis'          => $akun->nis,
            'nip'          => $akun->nip,
            'nis_nip'      => $akun->nis_nip,
            'role'         => $akun->role,
            'role_label'   => $akun->role_label,
            'nomor_kontak' => $akun->nomor_kontak,
            'username'     => $akun->username,
            'email'        => $akun->email,
            'foto'         => $akun->foto ? asset('storage/' . $akun->foto) : null,
            'is_active'    => $akun->is_active,
            'created_at'   => $akun->created_at?->format('d M Y, H:i'),
        ]);
    }

    /**
     * Update data akun yang ada.
     */
    public function update(Request $request, $id)
    {
        $akun = Akun::findOrFail($id);

        $request->validate([
            'nama'        => 'required|string|max:255',
            'nis_nip'     => 'required|string|max:20',
            'role'        => 'required|in:siswa,admin_sarana,admin_sistem',
            'nomor_kontak'=> 'nullable|string|max:20',
            'username'    => ['required', 'string', 'max:255', Rule::unique('akun', 'username')->ignore($akun->id_akun, 'id_akun')],
            'email'       => ['nullable', 'email', 'max:255', Rule::unique('akun', 'email')->ignore($akun->id_akun, 'id_akun')],
            'password'    => ['nullable', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
            'foto'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nama.required'     => 'Nama wajib diisi.',
            'nis_nip.required'  => 'NIS/NIP wajib diisi.',
            'role.required'     => 'Role wajib dipilih.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan.',
            'email.unique'      => 'Email sudah digunakan.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        $nisNip = trim($request->nis_nip);
        $role = $request->role;
        $nis = null;
        $nip = null;

        // Validasi NIS/NIP sesuai role
        if ($role === 'siswa') {
            $siswa = Siswa::where('nis', $nisNip)->first();
            if (!$siswa) {
                return back()->withErrors(['nis_nip' => 'NIS tidak ditemukan di data siswa.'])->withInput();
            }
            $existingAkun = Akun::where('nis', $nisNip)->where('id_akun', '!=', $akun->id_akun)->first();
            if ($existingAkun) {
                return back()->withErrors(['nis_nip' => 'NIS ini sudah memiliki akun terdaftar.'])->withInput();
            }
            $nis = $nisNip;
        } elseif ($role === 'admin_sarana') {
            $pegawai = Pegawai::where('nip', $nisNip)->first();
            if (!$pegawai) {
                return back()->withErrors(['nis_nip' => 'NIP tidak ditemukan di data pegawai.'])->withInput();
            }
            $existingAkun = Akun::where('nip', $nisNip)->where('id_akun', '!=', $akun->id_akun)->first();
            if ($existingAkun) {
                return back()->withErrors(['nis_nip' => 'NIP ini sudah memiliki akun terdaftar.'])->withInput();
            }
            $nip = $nisNip;
        }

        // Upload foto baru jika ada
        if ($request->hasFile('foto')) {
            // Hapus foto lama
            if ($akun->foto && Storage::disk('public')->exists($akun->foto)) {
                Storage::disk('public')->delete($akun->foto);
            }
            $akun->foto = $request->file('foto')->store('avatars', 'public');
        }

        $akun->nis = $nis;
        $akun->nip = $nip;
        $akun->nama = $request->nama;
        $akun->nomor_kontak = $request->nomor_kontak;
        $akun->email = $request->email;
        $akun->role = $role;
        $akun->username = $request->username;

        // Update password hanya jika diisi
        if ($request->filled('password')) {
            $akun->password = $request->password; // Auto-hashed via $casts
        }

        $akun->save();

        return redirect()->route('admin.sistem.accounts')
            ->with('success', 'Data akun berhasil diperbarui.');
    }

    /**
     * Deactivate akun (soft toggle is_active).
     */
    public function destroy($id)
    {
        $akun = Akun::findOrFail($id);

        // Jangan biarkan admin mendeactivate dirinya sendiri
        if ($akun->id_akun === auth()->user()->id_akun) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $akun->is_active = !$akun->is_active;
        $akun->save();

        $status = $akun->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->route('admin.sistem.accounts')
            ->with('success', "Akun {$akun->nama} berhasil {$status}.");
    }

    /**
     * Reset password akun ke default 'password123'.
     */
    public function resetPassword($id)
    {
        $akun = Akun::findOrFail($id);

        $akun->password = 'password123'; // Auto-hashed via $casts
        $akun->save();

        return redirect()->route('admin.sistem.accounts')
            ->with('success', "Password akun {$akun->nama} berhasil di-reset ke default.");
    }
}
