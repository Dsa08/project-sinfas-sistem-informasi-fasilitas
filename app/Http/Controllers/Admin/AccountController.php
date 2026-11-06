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
 * 6. Menerbitkan kata sandi sementara terstruktur (format SMK11-XXXX) dan pembuatan akun massal otomatis bagi siswa.
 */
class AccountController extends Controller
{
    /**
     * Menampilkan daftar seluruh akun pengguna selain admin sistem utama.
     * Mendukung pencarian multi-field (nama, username, NIS, NIP, email), paginasi dinamis, dan pengurutan kolom.
     *
     * @param Request $request Request HTTP yang memuat query string pencarian, penyortiran, dan per_page.
     * @return View Tampilan view 'admin.system.accounts' dengan data akun terpaginasi dan metrik total akun.
     */
    public function index(Request $request)
    {
        // 1. Inisialisasi query: kecualikan akun bertipe admin sistem
        $query = Akun::where('role', '!=', 'admin_sistem');

        // 2. Pencarian data akun berdasarkan nama, username, email, atau NIS/NIP pada relasi profil
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('siswa', fn ($sq) => $sq->where('nis', 'like', "%{$search}%"))
                    ->orWhereHas('pegawai', fn ($pq) => $pq->where('nip', 'like', "%{$search}%"))
                    ->orWhereHas('staffSarana', fn ($stq) => $stq->where('nip', 'like', "%{$search}%"));
            });
        }

        // 3. Penyortiran kolom: default data paling baru di atas (updated_at desc)
        $sortField = $request->input('sort');
        $sortDir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['nama', 'role', 'is_active', 'created_at', 'updated_at'];

        if (in_array($sortField, ['nis', 'nip', 'nis_nip'])) {
            $query->leftJoin('siswa', 'akun.id_akun', '=', 'siswa.id_akun')
                ->leftJoin('pegawai', 'akun.id_akun', '=', 'pegawai.id_akun')
                ->select('akun.*')
                ->orderByRaw("COALESCE(siswa.nis, pegawai.nip) {$sortDir}");
        } elseif ($sortField && in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        } else {
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
     * Menyimpan rekaman akun pengguna baru ke basis data dengan validasi identitas master sekolah.
     * Mendukung opsi pembuatan kata sandi sementara otomatis terstruktur (gaya Scola).
     *
     * Prasyarat:
     * - Nomor identitas NIS (untuk siswa) atau NIP (untuk pegawai/sarana) harus terdaftar di master data sekolah.
     * - Username dan email harus unik di tabel `akun`.
     * - Jika mode sandi otomatis aktif, input password manual tidak diwajibkan.
     *
     * Efek Samping:
     * - Menyimpan avatar baru ke disk penyimpanan dengan optimasi WebP.
     * - Membuat baris baru di tabel `akun` dan mengaitkan ID akun ke tabel profil (`siswa`, `pegawai`, atau `staff_sarana`).
     * - Jika sandi otomatis dipilih, menandai `must_change_password = true` dan mem-flash sandi sementara ke session.
     *
     * @param Request $request Request HTTP yang membawa seluruh data akun baru.
     * @return RedirectResponse Pengalihan kembali ke daftar akun dengan pesan sukses.
     */
    public function store(Request $request)
    {
        $autoPassword = $request->boolean('auto_password');

        // 1. Validasi struktur data input (fleksibel terhadap mode kata sandi manual atau otomatis)
        $request->validate([
            'nama' => 'required|string|max:255',
            'nis_nip' => 'required|string|max:20',
            'role' => 'required|in:siswa,pegawai,admin_sarana',
            'nomor_kontak' => 'nullable|string|max:20',
            'username' => 'required|string|max:255|unique:akun,username',
            'email' => 'nullable|email|max:255|unique:akun,email',
            'password' => $autoPassword ? 'nullable' : ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nis_nip.required' => 'NIS/NIP wajib diisi.',
            'role.required' => 'Role wajib dipilih.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi jika tidak memilih opsi otomatis.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $role = $request->role;
        $identity = $this->resolveIdentity($role, trim($request->nis_nip));

        // 2. Tentukan kata sandi: buat sandi sementara terstruktur jika mode otomatis dipilih
        $plainPassword = $autoPassword ? ('SMK11-' . random_int(1000, 9999)) : $request->password;
        $mustChangePassword = $autoPassword;

        // 3. Upload berkas foto avatar pengguna jika disediakan
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = app(WebpImageOptimizer::class)
                ->storeOnDisk($request->file('foto'), 'avatars', 'public_uploads', 'avatar');
        }

        // 4. Eksekusi pembuatan akun baru (tanpa kolom nis/nip di akun sesuai ERD 2)
        $akunBaru = null;
        DB::transaction(function () use ($request, $role, $identity, $fotoPath, $plainPassword, $mustChangePassword, &$akunBaru) {
            $akunBaru = Akun::create([
                'nama'                 => $request->nama,
                'nomor_kontak'         => $request->nomor_kontak,
                'email'                => $request->email,
                'role'                 => $role,
                'username'             => $request->username,
                'password'             => $plainPassword,
                'foto'                 => $fotoPath,
                'is_active'            => true,
                'must_change_password' => $mustChangePassword,
            ]);

            $this->syncUserProfile($akunBaru, $identity, $role);
        });

        $redirect = redirect()->route('admin.sistem.accounts')
            ->with('success', 'Akun pengguna berhasil ditambahkan.');

        // 5. Jika menggunakan kata sandi sementara, sertakan flash session agar dapat disalin Admin
        if ($autoPassword && $akunBaru) {
            $redirect->with('temporary_password', $plainPassword)
                ->with('temporary_password_account', $akunBaru->nama);
        }

        return $redirect;
    }

    /**
     * Mengambil rincian data lengkap akun pengguna dalam format JSON untuk kebutuhan modal dialog AJAX.
     * Mengikutsertakan relasi master identitas NIS/NIP dan 10 riwayat transaksi peminjaman terakhir.
     *
     * @param int $id ID unik akun pada tabel `akun`.
     * @return JsonResponse Data profil akun dan riwayat pinjaman dalam format JSON.
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
                            'dibatalkan' => 'Dibatalkan peminjam',
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
            'nis' => $akun->siswa?->nis,
            'nip' => $akun->pegawai?->nip ?? $akun->staffSarana?->nip,
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
     * Memperbarui informasi akun pengguna yang telah terdaftar serta menyinkronkan data profilnya.
     *
     * Prasyarat:
     * - Username dan email harus unik terhadap akun lain (mengabaikan ID akun saat ini).
     *
     * Efek Samping:
     * - Mengganti avatar fisik lama di server jika ada berkas foto baru yang diunggah.
     * - Memperbarui rekaman di tabel `akun` dan tabel profil induk.
     *
     * @param Request $request Request HTTP yang memuat data pembaruan akun.
     * @param int $id ID unik akun yang akan diperbarui.
     * @return RedirectResponse Pengalihan kembali ke panel kelola akun dengan notifikasi sukses.
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

            $akun->nama = $request->nama;
            $akun->nomor_kontak = $request->nomor_kontak;
            $akun->email = $request->email;
            $akun->role = $role;
            $akun->username = $request->username;
            if ($request->filled('password')) {
                $akun->password = $request->password;
            }
            $akun->save();

            $this->syncUserProfile($akun, $identity, $role);
        });

        if ($oldFotoPath) {
            app(PublicUploadStorage::class)->delete($oldFotoPath);
        }

        return redirect()->route('admin.sistem.accounts')
            ->with('success', 'Data akun berhasil diperbarui.');
    }

    /**
     * Mengubah status aktif akun pengguna (mengaktifkan kembali atau menonaktifkan akun).
     * Mencegah admin sistem menonaktifkan akun miliknya sendiri untuk mencegah insiden penguncian sistem (lockout).
     *
     * Efek Samping:
     * - Membalik nilai boolean pada kolom `is_active` di tabel `akun`.
     *
     * @param int $id ID unik akun yang status keaktifannya akan diubah.
     * @return RedirectResponse Pengalihan kembali ke panel kelola akun dengan status pesan terbaru.
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

    /**
     * Memvalidasi dan mencari profil master sekolah yang berelasi dengan nomor identitas NIS/NIP yang diinputkan.
     *
     * @param string $role Peran akun yang diajukan ('siswa', 'pegawai', atau 'admin_sarana').
     * @param string $identity Nomor NIS atau NIP yang diinputkan.
     * @param Akun|null $currentAccount Model akun yang sedang diedit (jika operasi update).
     * @return array<string, mixed> Array berisi nomor NIS/NIP dan objek model profil master sekolah.
     * @throws ValidationException Jika identitas tidak ditemukan atau sudah dipakai akun lain.
     */
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
        if ($staffAccountId && (int) $staffAccountId !== (int) ($currentAccount?->id_akun ?? 0)) {
            throw ValidationException::withMessages(['nis_nip' => 'Pegawai ini sudah memiliki akun staff sarana.']);
        }

        return ['nis' => null, 'nip' => $profile->nip, 'profile' => $profile];
    }

    /**
     * Menghubungkan relasi akun login ke tabel profil master sekolah (`staff_sarana`, `siswa`, atau `pegawai`).
     *
     * Efek Samping:
     * - Membuat record di tabel `staff_sarana` atau memperbarui kolom `id_akun` pada tabel `siswa`/`pegawai`.
     *
     * @param Akun $akun Model akun pengguna yang baru dibuat atau diperbarui.
     * @param array<string, mixed> $identity Data profil hasil resolusi identitas sekolah.
     * @param string $role Peran akun yang ditetapkan.
     * @return void
     */
    private function syncUserProfile(Akun $akun, array $identity, string $role): void
    {
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
    }

    /**
     * Menerbitkan kata sandi sementara terstruktur yang ramah-ketik (format SMK11-XXXX) untuk pemulihan akses akun oleh Admin Sistem.
     * Menandai akun dengan `must_change_password = true` agar pengguna dipaksa mengganti sandi saat login berikutnya.
     *
     * Prasyarat / Kondisi:
     * - Akun dengan ID yang diberikan harus terdaftar dalam tabel `akun`.
     *
     * Efek Samping:
     * - Memperbarui hash kata sandi pada basis data dan menyetel flag `must_change_password = true`.
     *
     * @param int $id ID unik akun yang sandinya di-reset.
     * @return RedirectResponse Pengalihan kembali dengan flash session kata sandi sementara yang hanya ditampilkan satu kali.
     */
    public function resetPassword($id)
    {
        $akun = Akun::findOrFail($id);

        // Hasilkan kata sandi sementara terstruktur agar mudah diketik pada papan ketik ponsel siswa atau pegawai
        $temporaryPassword = 'SMK11-' . random_int(1000, 9999);
        $akun->password = $temporaryPassword;
        $akun->must_change_password = true;
        $akun->save();

        return redirect()->route('admin.sistem.accounts')
            ->with('temporary_password', $temporaryPassword)
            ->with('temporary_password_account', $akun->nama);
    }

    /**
     * Membuat akun login secara massal untuk seluruh data siswa terdaftar yang belum memiliki akun sistem.
     * Menggunakan Nomor Induk Siswa (NIS) sebagai username awal dan kata sandi sementara ramah-ketik (format SMK11-XXXX).
     * Seluruh akun hasil pembuatan massal ditandai `must_change_password = true` demi menjaga keamanan akun sejak awal.
     *
     * Prasyarat / Kondisi:
     * - Terdapat baris data pada tabel `siswa` dengan foreign key `id_akun` bernilai null.
     *
     * Efek Samping:
     * - Menyimpan rekaman baru pada tabel `akun` dengan peran 'siswa'.
     * - Memperbarui foreign key `id_akun` pada baris tabel `siswa` yang bersangkutan.
     *
     * @return RedirectResponse Pengalihan kembali ke panel kelola akun beserta rekapitulasi jumlah akun yang berhasil dibuat.
     */
    public function batchGenerateStudentAccounts(): RedirectResponse
    {
        // 1. Ambil data master siswa yang belum memiliki relasi akun login
        $unlinkedStudents = Siswa::whereNull('id_akun')->get();

        if ($unlinkedStudents->isEmpty()) {
            return redirect()->route('admin.sistem.accounts')
                ->with('error', 'Semua siswa yang terdaftar di master data sudah memiliki akun sistem.');
        }

        $createdCount = 0;
        $skippedCount = 0;

        // 2. Iterasi setiap siswa untuk membuatkan akun terstruktur secara atomik
        foreach ($unlinkedStudents as $siswa) {
            $username = trim($siswa->nis);

            // Lewati jika username dari NIS ini secara tak terduga telah digunakan oleh akun lain
            if (Akun::where('username', $username)->exists()) {
                $skippedCount++;
                continue;
            }

            // Gunakan email siswa jika valid dan belum terpakai pada tabel akun
            $email = !empty($siswa->email) && !Akun::where('email', $siswa->email)->exists()
                ? $siswa->email
                : null;

            // Hasilkan sandi awal terstruktur yang mudah disalin dan dibagikan oleh staf tata usaha
            $temporaryPassword = 'SMK11-' . random_int(1000, 9999);

            try {
                DB::transaction(function () use ($siswa, $username, $email, $temporaryPassword, &$createdCount) {
                    $akun = Akun::create([
                        'nama'                 => $siswa->nama,
                        'nomor_kontak'         => $siswa->no_hp,
                        'email'                => $email,
                        'role'                 => 'siswa',
                        'username'             => $username,
                        'password'             => $temporaryPassword,
                        'is_active'            => true,
                        'must_change_password' => true,
                    ]);

                    // Hubungkan akun baru ke profil master siswa terkait
                    $siswa->id_akun = $akun->id_akun;
                    $siswa->save();

                    $createdCount++;
                });
            } catch (\Throwable $e) {
                // Lewati dan catat kegagalan individu agar proses akun siswa lainnya tetap berjalan
                $skippedCount++;
            }
        }

        $message = "Berhasil membuat {$createdCount} akun siswa otomatis dengan sandi sementara (SMK11-XXXX).";
        if ($skippedCount > 0) {
            $message .= " Sebanyak {$skippedCount} data siswa dilewati karena kendala data duplikat.";
        }

        return redirect()->route('admin.sistem.accounts')
            ->with('success', $message);
    }
}
