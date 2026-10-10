<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Pegawai;
use App\Models\Peminjaman;
use App\Models\Siswa;
use App\Models\StaffSarana;
use App\Services\PublicUploadStorage;
use App\Services\WebpImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controller AccountController
 *
 * Mengelola administrasi data akun pengguna aplikasi SINFAS (Khusus Role Admin Sistem):
 * 1. Menampilkan seluruh akun (Siswa & Admin Sarana) dengan fitur pencarian, penyortiran, dan paginasi.
 * 2. Membuat akun pengguna baru dengan validasi integritas terhadap data master sekolah (NIS Siswa / NIP Pegawai).
 * 3. Menampilkan detail akun via format JSON untuk keperluan modal antarmuka.
 * 4. Memperbarui identitas, kontak, role, username, dan password akun.
 * 5. Fitur soft-deactivate/toggle status aktif akun (mencegah akun menonaktifkan dirinya sendiri).
 * 6. Membuat kata sandi sementara acak untuk pemulihan akun oleh admin sistem.
 */
class AccountController extends Controller
{
    /**
     * Menampilkan daftar seluruh akun pengguna selain admin sistem utama.
     * Mendukung multi-field search (nama, username, nis, nip, email) dan sorting dinamis.
     *
     * @return View
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

        // 4. Ambil data terpaginasi (10, 25, 50, 100 akun per halaman)
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100]) ? (int) $request->input('per_page') : 10;
        $accounts = $query->paginate($perPage)->withQueryString();

        // 5. Statistik ringkas metrik akun untuk widget header
        $totalAccounts = Akun::count();
        $totalActive = Akun::active()->count();

        return view('admin.system.accounts', compact('accounts', 'totalAccounts', 'totalActive'));
    }

    /**
     * Menyimpan data akun pengguna baru ke dalam sistem.
     * Memvalidasi apakah NIS siswa atau NIP pegawai terdaftar resmi di basis data sekolah.
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Validasi struktur data input
        $request->validate([
            'nama' => 'required|string|max:255',
            'nis_nip' => 'required|string|max:20',
            'role' => 'required|in:siswa,pegawai,admin_sarana',
            'nomor_kontak' => 'nullable|string|max:20',
            'username' => 'required|string|max:255|unique:akun,username',
            'email' => 'nullable|email|max:255|unique:akun,email',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nis_nip.required' => 'NIS/NIP wajib diisi.',
            'role.required' => 'Role wajib dipilih.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $role = $request->role;
        $identity = $this->resolveIdentity($role, trim($request->nis_nip));

        // 3. Upload berkas foto avatar pengguna jika disediakan
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = app(WebpImageOptimizer::class)
                ->storeOnDisk($request->file('foto'), 'avatars', 'public_uploads', 'avatar');
        }

        // 4. Eksekusi pembuatan akun baru
        DB::transaction(function () use ($request, $role, $identity, $fotoPath) {
            $akun = Akun::create([
                'nis' => $identity['nis'],
                'nip' => $identity['nip'],
                'nama' => $request->nama,
                'nomor_kontak' => $request->nomor_kontak,
                'email' => $request->email,
                'role' => $role,
                'username' => $request->username,
                'password' => $request->password,
                'foto' => $fotoPath,
                'is_active' => true,
            ]);

            if ($role === 'admin_sarana') {
                StaffSarana::create([
                    'nip' => $identity['nip'],
                    'id_akun' => $akun->id_akun,
                    'nama' => $akun->nama,
                    'no_hp' => $akun->nomor_kontak,
                ]);
            } else {
                $identity['profile']->id_akun = $akun->id_akun;
                $identity['profile']->nama = $akun->nama;
                $identity['profile']->email = $akun->email;
                $identity['profile']->no_hp = $akun->nomor_kontak;
                $identity['profile']->save();
            }
        });

        return redirect()->route('admin.sistem.accounts')
            ->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    /**
     * Mengambil detail akun dalam format JSON untuk kebutuhan modal view/edit AJAX.
     *
     * @param  int  $id  ID Akun
     * @return JsonResponse
     */
    public function show($id)
    {
        $akun = Akun::findOrFail($id);
        $loanHistory = $akun->isPeminjam()
            ? Peminjaman::with(['barang', 'pengembalian'])
                ->where('id_akun', $akun->id_akun)
                ->orderByDesc('tanggal_pinjam')
                ->limit(10)
                ->get()
                ->map(fn (Peminjaman $loan) => [
                    'item' => $loan->barang->nama_barang ?? 'Barang tidak ditemukan',
                    'date' => $loan->tanggal_pinjam?->format('d M Y') ?? '-',
                    'purpose' => $loan->keterangan_penggunaan ?: '-',
                    'location' => $loan->lokasi_penggunaan ?: '-',
                    'status' => $loan->pengembalian
                        ? ($loan->pengembalian->kondisi_barang ? 'Dikembalikan' : 'Menunggu verifikasi pengembalian')
                        : match ($loan->status_pengajuan) {
                            'disetujui' => 'Sedang dipinjam',
                            'menunggu' => 'Menunggu persetujuan',
                            'ditolak' => 'Ditolak',
                            default => ucfirst($loan->status_pengajuan),
                        },
                    'return_date' => $loan->pengembalian?->tanggal_kembali?->format('d M Y'),
                    'condition' => $loan->pengembalian?->kondisi_barang,
                    'return_note' => $loan->pengembalian?->catatan,
                ])
            : collect();

        return response()->json([
            'id_akun' => $akun->id_akun,
            'nama' => $akun->nama,
            'nis' => $akun->nis,
            'nip' => $akun->nip,
            'nis_nip' => $akun->nis_nip,
            'role' => $akun->role,
            'role_label' => $akun->role_label,
            'nomor_kontak' => $akun->nomor_kontak,
            'username' => $akun->username,
            'email' => $akun->email,
            'foto' => $akun->foto_url,
            'is_active' => $akun->is_active,
            'created_at' => $akun->created_at?->format('d M Y, H:i'),
            'loan_history' => $loanHistory,
        ]);
    }

    /**
     * Memperbarui informasi akun pengguna yang telah ada.
     *
     * @param  int  $id  ID Akun
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $akun = Akun::findOrFail($id);

        // 1. Validasi input dengan pengecualian unique check untuk record saat ini
        $request->validate([
            'nama' => 'required|string|max:255',
            'nis_nip' => 'required|string|max:20',
            'role' => 'required|in:siswa,pegawai,admin_sarana',
            'nomor_kontak' => 'nullable|string|max:20',
            'username' => ['required', 'string', 'max:255', Rule::unique('akun', 'username')->ignore($akun->id_akun, 'id_akun')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('akun', 'email')->ignore($akun->id_akun, 'id_akun')],
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nis_nip.required' => 'NIS/NIP wajib diisi.',
            'role.required' => 'Role wajib dipilih.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.unique' => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $role = $request->role;
        $identity = $this->resolveIdentity($role, trim($request->nis_nip), $akun);

        // 3. Penggantian berkas avatar jika ada file baru diunggah
        $oldFotoPath = null;
        if ($request->hasFile('foto')) {
            $newFotoPath = app(WebpImageOptimizer::class)
                ->storeOnDisk($request->file('foto'), 'avatars', 'public_uploads', 'avatar');

            $oldFotoPath = $akun->foto;
            $akun->foto = $newFotoPath;
        }

        DB::transaction(function () use ($request, $akun, $role, $identity) {
            Siswa::where('id_akun', $akun->id_akun)->update(['id_akun' => null]);
            Pegawai::where('id_akun', $akun->id_akun)->update(['id_akun' => null]);
            StaffSarana::where('id_akun', $akun->id_akun)->delete();

            $akun->nis = $identity['nis'];
            $akun->nip = $identity['nip'];
            $akun->nama = $request->nama;
            $akun->nomor_kontak = $request->nomor_kontak;
            $akun->email = $request->email;
            $akun->role = $role;
            $akun->username = $request->username;
            if ($request->filled('password')) {
                $akun->password = $request->password;
            }
            $akun->save();

            if ($role === 'admin_sarana') {
                StaffSarana::create([
                    'nip' => $identity['nip'],
                    'id_akun' => $akun->id_akun,
                    'nama' => $akun->nama,
                    'no_hp' => $akun->nomor_kontak,
                ]);
            } else {
                $identity['profile']->id_akun = $akun->id_akun;
                $identity['profile']->nama = $akun->nama;
                $identity['profile']->email = $akun->email;
                $identity['profile']->no_hp = $akun->nomor_kontak;
                $identity['profile']->save();
            }
        });

        if ($oldFotoPath) {
            app(PublicUploadStorage::class)->delete($oldFotoPath);
        }

        return redirect()->route('admin.sistem.accounts')
            ->with('success', 'Data akun berhasil diperbarui.');
    }

    /**
     * Mengaktifkan atau menonaktifkan akun pengguna (toggle is_active).
     * Mencegah admin menonaktifkan akun dirinya sendiri.
     *
     * @param  int  $id  ID Akun
     * @return RedirectResponse
     */
    public function destroy($id)
    {
        $akun = Akun::findOrFail($id);

        // Cegah admin menonaktifkan akun sendiri untuk menghindari lockout
        if ($akun->id_akun === auth()->user()->id_akun) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $akun->is_active = ! $akun->is_active;
        $akun->save();

        $status = $akun->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->route('admin.sistem.accounts')
            ->with('success', "Akun {$akun->nama} berhasil {$status}.");
    }

    private function resolveIdentity(string $role, string $identity, ?Akun $currentAccount = null): array
    {
        if ($role === 'siswa') {
            $profile = Siswa::find($identity);
            if (! $profile) {
                throw ValidationException::withMessages(['nis_nip' => 'Data siswa dengan NIS tersebut tidak ditemukan.']);
            }
            if ($profile->id_akun && (int) $profile->id_akun !== (int) ($currentAccount?->id_akun ?? 0)) {
                throw ValidationException::withMessages(['nis_nip' => 'NIS ini sudah terhubung dengan akun lain.']);
            }

            return ['nis' => $profile->nis, 'nip' => null, 'profile' => $profile];
        }

        $profile = Pegawai::find($identity);
        if (! $profile) {
            throw ValidationException::withMessages(['nis_nip' => 'Data pegawai dengan NIP tersebut tidak ditemukan.']);
        }

        if ($role === 'pegawai') {
            if ($profile->id_akun && (int) $profile->id_akun !== (int) ($currentAccount?->id_akun ?? 0)) {
                throw ValidationException::withMessages(['nis_nip' => 'Pegawai ini sudah memiliki akun pegawai.']);
            }

            return ['nis' => null, 'nip' => $profile->nip, 'profile' => $profile];
        }

        $staffAccountId = StaffSarana::where('nip', $profile->nip)->value('id_akun');
        if ($staffAccountId && (int) $staffAccountId !== (int) $currentAccount?->id_akun) {
            throw ValidationException::withMessages(['nis_nip' => 'Pegawai ini sudah memiliki akun staff sarana.']);
        }

        return ['nis' => null, 'nip' => $profile->nip, 'profile' => $profile];
    }

    /**
     * Membuat sandi sementara acak dan menampilkannya sekali kepada admin.
     * Sandi tidak pernah disimpan atau dicatat dalam bentuk teks biasa.
     *
     * @param  int  $id  ID Akun
     * @return RedirectResponse
     */
    public function resetPassword($id)
    {
        $akun = Akun::findOrFail($id);

        $temporaryPassword = Str::password(20, true, true, false, false);
        $akun->password = $temporaryPassword;
        $akun->must_change_password = true;
        $akun->save();

        return redirect()->route('admin.sistem.accounts')
            ->with('temporary_password', $temporaryPassword)
            ->with('temporary_password_account', $akun->nama);
    }
}
