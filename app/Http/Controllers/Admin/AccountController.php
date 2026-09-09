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

/**
 * Controller AccountController
 * 
 * Mengelola administrasi data akun pengguna aplikasi SINFAS (Khusus Role Admin Sistem):
 * 1. Menampilkan seluruh akun (Siswa & Admin Sarana) dengan fitur pencarian, penyortiran, dan paginasi.
 * 2. Membuat akun pengguna baru dengan validasi integritas terhadap data master sekolah (NIS Siswa / NIP Pegawai).
 * 3. Menampilkan detail akun via format JSON untuk keperluan modal antarmuka.
 * 4. Memperbarui identitas, kontak, role, username, dan password akun.
 * 5. Fitur soft-deactivate/toggle status aktif akun (mencegah akun menonaktifkan dirinya sendiri).
 * 6. Fitur reset password darurat ke nilai default ('password123') oleh admin sistem.
 */
class AccountController extends Controller
{
    /**
     * Menampilkan daftar seluruh akun pengguna selain admin sistem utama.
     * Mendukung multi-field search (nama, username, nis, nip, email) dan sorting dinamis.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 1. Inisialisasi query: kecualikan akun bertipe admin sistem
        $query = Akun::where('role', '!=', 'admin_sistem');

        // 2. Pencarian data akun berdasarkan nama, username, nis, nip, atau email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 3. Penyortiran kolom (Sort by field & direction): default data paling baru di atas (updated_at desc)
        $sortField = $request->input('sort');
        $sortDir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['nama', 'nis', 'nip', 'role', 'is_active', 'created_at', 'updated_at'];

        if ($sortField === 'nis' || $sortField === 'nis_nip') {
            $query->orderByRaw("COALESCE(nis, nip) {$sortDir}");
        } elseif ($sortField && in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        } else {
            // Default: data paling baru di atas
            $query->orderBy('updated_at', 'desc');
        }

        // 4. Ambil data terpaginasi (10 akun per halaman)
        $accounts = $query->paginate(10)->withQueryString();

        // 5. Statistik ringkas metrik akun untuk widget header
        $totalAccounts = Akun::count();
        $totalActive = Akun::active()->count();

        return view('admin.system.accounts', compact('accounts', 'totalAccounts', 'totalActive'));
    }

    /**
     * Menyimpan data akun pengguna baru ke dalam sistem.
     * Memvalidasi apakah NIS siswa atau NIP pegawai terdaftar resmi di basis data sekolah.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Validasi struktur data input
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
            'nama.required'      => 'Nama wajib diisi.',
            'nis_nip.required'   => 'NIS/NIP wajib diisi.',
            'role.required'      => 'Role wajib dipilih.',
            'username.required'  => 'Username wajib diisi.',
            'username.unique'    => 'Username sudah digunakan.',
            'email.unique'       => 'Email sudah digunakan.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $nisNip = trim($request->nis_nip);
        $role = $request->role;
        $nis = null;
        $nip = null;

        // 2. Validasi keterkaitan NIS/NIP dengan data referensi master
        if ($role === 'siswa') {
            $siswa = Siswa::where('nis', $nisNip)->first();
            if (!$siswa) {
                return back()->withErrors(['nis_nip' => 'NIS tidak ditemukan di data master siswa.'])->withInput();
            }
            if (Akun::where('nis', $nisNip)->exists()) {
                return back()->withErrors(['nis_nip' => 'NIS ini sudah memiliki akun terdaftar.'])->withInput();
            }
            $nis = $nisNip;
        } elseif ($role === 'admin_sarana') {
            $pegawai = Pegawai::where('nip', $nisNip)->first();
            if (!$pegawai) {
                return back()->withErrors(['nis_nip' => 'NIP tidak ditemukan di data master pegawai.'])->withInput();
            }
            if (Akun::where('nip', $nisNip)->exists()) {
                return back()->withErrors(['nis_nip' => 'NIP ini sudah memiliki akun terdaftar.'])->withInput();
            }
            $nip = $nisNip;
        }

        // 3. Upload berkas foto avatar pengguna jika disediakan
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('avatars', 'public');
        }

        // 4. Eksekusi pembuatan akun baru
        Akun::create([
            'nis'          => $nis,
            'nip'          => $nip,
            'nama'         => $request->nama,
            'nomor_kontak' => $request->nomor_kontak,
            'email'        => $request->email,
            'role'         => $role,
            'username'     => $request->username,
            'password'     => $request->password, // Password otomatis di-hash via cast Eloquent
            'foto'         => $fotoPath,
            'is_active'    => true,
        ]);

        return redirect()->route('admin.sistem.accounts')
            ->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    /**
     * Mengambil detail akun dalam format JSON untuk kebutuhan modal view/edit AJAX.
     *
     * @param  int  $id  ID Akun
     * @return \Illuminate\Http\JsonResponse
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
     * Memperbarui informasi akun pengguna yang telah ada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  ID Akun
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $akun = Akun::findOrFail($id);

        // 1. Validasi input dengan pengecualian unique check untuk record saat ini
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
            'nama.required'      => 'Nama wajib diisi.',
            'nis_nip.required'   => 'NIS/NIP wajib diisi.',
            'role.required'      => 'Role wajib dipilih.',
            'username.required'  => 'Username wajib diisi.',
            'username.unique'    => 'Username sudah digunakan.',
            'email.unique'       => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $nisNip = trim($request->nis_nip);
        $role = $request->role;
        $nis = null;
        $nip = null;

        // 2. Validasi integritas NIS/NIP
        if ($role === 'siswa') {
            $siswa = Siswa::where('nis', $nisNip)->first();
            if (!$siswa) {
                return back()->withErrors(['nis_nip' => 'NIS tidak ditemukan di data siswa.'])->withInput();
            }
            $existingAkun = Akun::where('nis', $nisNip)->where('id_akun', '!=', $akun->id_akun)->first();
            if ($existingAkun) {
                return back()->withErrors(['nis_nip' => 'NIS ini sudah terhubung dengan akun lain.'])->withInput();
            }
            $nis = $nisNip;
        } elseif ($role === 'admin_sarana') {
            $pegawai = Pegawai::where('nip', $nisNip)->first();
            if (!$pegawai) {
                return back()->withErrors(['nis_nip' => 'NIP tidak ditemukan di data pegawai.'])->withInput();
            }
            $existingAkun = Akun::where('nip', $nisNip)->where('id_akun', '!=', $akun->id_akun)->first();
            if ($existingAkun) {
                return back()->withErrors(['nis_nip' => 'NIP ini sudah terhubung dengan akun lain.'])->withInput();
            }
            $nip = $nisNip;
        }

        // 3. Penggantian berkas avatar jika ada file baru diunggah
        if ($request->hasFile('foto')) {
            if ($akun->foto && Storage::disk('public')->exists($akun->foto)) {
                Storage::disk('public')->delete($akun->foto);
            }
            $akun->foto = $request->file('foto')->store('avatars', 'public');
        }

        // 4. Update data profil
        $akun->nis = $nis;
        $akun->nip = $nip;
        $akun->nama = $request->nama;
        $akun->nomor_kontak = $request->nomor_kontak;
        $akun->email = $request->email;
        $akun->role = $role;
        $akun->username = $request->username;

        // 5. Update password hanya jika kolom password diisi oleh admin
        if ($request->filled('password')) {
            $akun->password = $request->password;
        }

        $akun->save();

        return redirect()->route('admin.sistem.accounts')
            ->with('success', 'Data akun berhasil diperbarui.');
    }

    /**
     * Mengaktifkan atau menonaktifkan akun pengguna (toggle is_active).
     * Mencegah admin menonaktifkan akun dirinya sendiri.
     *
     * @param  int  $id  ID Akun
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $akun = Akun::findOrFail($id);

        // Cegah admin menonaktifkan akun sendiri untuk menghindari lockout
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
     * Mereset kata sandi akun pengguna ke default ('password123').
     * Berguna jika pengguna lupa sandi dan tidak dapat mengakses email.
     *
     * @param  int  $id  ID Akun
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword($id)
    {
        $akun = Akun::findOrFail($id);

        $akun->password = 'password123';
        $akun->save();

        return redirect()->route('admin.sistem.accounts')
            ->with('success', "Password akun {$akun->nama} berhasil di-reset ke nilai bawaan ('password123').");
    }
}
