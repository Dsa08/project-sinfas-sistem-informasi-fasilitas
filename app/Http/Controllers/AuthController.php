<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Models\Akun;
use App\Models\Siswa;
use App\Models\Pegawai;
use Carbon\Carbon;

/**
 * Controller AuthController
 * 
 * Mengelola seluruh alur otentikasi dan keamanan akun pengguna:
 * 1. Login multi-identitas (Username / NIS / NIP) dengan proteksi Brute Force (Rate Limiting).
 * 2. Registrasi mandiri akun siswa terverifikasi dengan referensi data master siswa.
 * 3. Pemulihan kata sandi (Forgot Password) via token kriptografis dan tautan email.
 * 4. Pembaruan kata sandi baru (Reset Password) dengan verifikasi masa aktif token (60 menit).
 * 5. Logout aman dengan invalidasi session dan regenerasi token CSRF.
 */
class AuthController extends Controller
{
    /**
     * Menampilkan formulir login aplikasi.
     * Jika pengguna sudah dalam status login, otomatis dialihkan ke dashboard sesuai perannya.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Memproses permohonan login dengan Rate Limiting anti brute force.
     * Pengguna dapat menginputkan Username, Email, NIS siswa, atau NIP pegawai.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // 1. Validasi keberadaan input identifier dan password
        $credentials = $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Username, Email, atau NIS/NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $identifier = $credentials['email'];
        $throttleKey = $this->throttleKey($request, $identifier);

        // 2. Proteksi Brute Force: batasi maksimal 5 percobaan gagal dalam periode tertentu
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        // 3. Cari akun berdasarkan salah satu identitas unik (username, email, nis, atau nip)
        $akun = Akun::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->orWhere('nis', $identifier)
            ->orWhere('nip', $identifier)
            ->first();

        // 4. Verifikasi kecocokan hash kata sandi dan status keaktifan akun
        if ($akun && Hash::check($credentials['password'], $akun->password)) {
            // Cek apakah akun dinonaktifkan oleh administrator
            if (isset($akun->is_active) && !$akun->is_active) {
                return back()->withErrors([
                    'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator Sistem.',
                ])->onlyInput('email');
            }

            // Bersihkan riwayat percobaan gagal pada Rate Limiter
            RateLimiter::clear($throttleKey);

            // Simpan autentikasi pada session web Laravel (dengan opsi Remember Me)
            Auth::login($akun, $request->filled('remember'));
            $request->session()->regenerate();

            return $this->redirectBasedOnRole($akun)->with('success', 'Selamat datang kembali, ' . $akun->nama . '!');
        }

        // 5. Catat kegagalan login ke Rate Limiter (decay time 60 detik)
        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'Akun atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Menampilkan formulir pendaftaran akun baru bagi siswa.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    /**
     * Memproses pendaftaran akun siswa baru dengan validasi data master sekolah.
     * Siswa hanya bisa mendaftar jika NIS telah terdaftar di database sekolah dan belum memiliki akun.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // 1. Validasi input formulir pendaftaran dengan aturan kata sandi kuat
        $request->validate([
            'full_name'      => 'required|string|max:255',
            'nis_nip'        => 'required|string|max:20',
            'email'          => 'required|string|email|max:255',
            'username'       => 'required|string|max:255|unique:akun,username',
            'contact_number' => 'nullable|string|max:20',
            'password'       => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'full_name.required'      => 'Nama lengkap wajib diisi.',
            'nis_nip.required'        => 'NIS atau NIP wajib diisi.',
            'email.required'          => 'Email wajib diisi.',
            'email.email'             => 'Format email tidak valid.',
            'username.required'       => 'Username wajib diisi.',
            'username.unique'         => 'Username ini sudah digunakan oleh akun lain.',
            'password.required'       => 'Password wajib diisi.',
            'password.confirmed'      => 'Konfirmasi password tidak cocok.',
        ]);

        $nis_nip = trim($request->nis_nip);
        $role = null;
        $nis = null;
        $nip = null;

        // 2. Verifikasi keberadaan identitas di tabel data master (Siswa atau Pegawai)
        $siswa = Siswa::where('nis', $nis_nip)->first();
        $pegawai = Pegawai::where('nip', $nis_nip)->first();

        if ($siswa) {
            // Cegah duplikasi pembuatan akun untuk NIS yang sama
            if (Akun::where('nis', $nis_nip)->exists()) {
                return back()->withErrors(['nis_nip' => 'NIS ini sudah terdaftar memiliki akun.'])->withInput();
            }
            $role = 'siswa';
            $nis = $nis_nip;
        } elseif ($pegawai) {
            // Cegah duplikasi pembuatan akun untuk NIP yang sama
            if (Akun::where('nip', $nis_nip)->exists()) {
                return back()->withErrors(['nis_nip' => 'NIP ini sudah terdaftar memiliki akun.'])->withInput();
            }
            $role = 'admin_sarana';
            $nip = $nis_nip;
        } else {
            // Tolak jika NIS/NIP tidak diakui oleh data sekolah
            return back()->withErrors([
                'nis_nip' => 'NIS atau NIP tidak terdaftar dalam data sekolah/instansi.',
            ])->withInput();
        }

        // 3. Simpan data akun baru dengan password terenkripsi Bcrypt
        $akun = Akun::create([
            'nis'          => $nis,
            'nip'          => $nip,
            'nama'         => $request->full_name,
            'email'        => $request->email,
            'nomor_kontak' => $request->contact_number,
            'role'         => $role,
            'username'     => $request->username,
            'password'     => Hash::make($request->password),
            'is_active'    => true,
        ]);

        // 4. Otomatis login dan regenerasi session
        Auth::login($akun);
        $request->session()->regenerate();

        return $this->redirectBasedOnRole($akun)->with('success', 'Registrasi berhasil! Selamat datang di SINFAS.');
    }

    /**
     * Memproses logout pengguna secara aman (menghapus session & token CSRF).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    /**
     * Helper: Menghasilkan kunci pembatas (rate limit key) berbasis kombinasi identifier dan alamat IP klien.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $identifier
     * @return string
     */
    protected function throttleKey(Request $request, string $identifier): string
    {
        return Str::transliterate(Str::lower($identifier) . '|' . $request->ip());
    }

    /**
     * Helper: Mengarahkan pengguna ke rute dashboard yang sesuai dengan peran (role) masing-masing.
     *
     * @param  \App\Models\Akun  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectBasedOnRole($user)
    {
        if ($user->role === 'siswa') {
            return redirect()->route('dashboard');
        } elseif ($user->role === 'admin_sarana') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'admin_sistem') {
            return redirect()->route('admin.sistem.dashboard');
        }

        return redirect('/');
    }

    /**
     * Menampilkan formulir permintaan lupa password.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showForgotPasswordForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.forgot-password');
    }

    /**
     * Memproses permohonan reset password, men-generate token unik aman 64-karakter,
     * dan mengirimkan tautan reset via email (atau logging URL darurat).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLink(Request $request)
    {
        // 1. Validasi input identifier
        $request->validate([
            'email' => 'required|string',
        ], [
            'email.required' => 'Email, Username, atau NIS/NIP wajib diisi.',
        ]);

        $identifier = trim($request->email);

        // 2. Pencarian akun pengguna melalui tabel akun maupun master siswa/pegawai
        $akun = Akun::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->orWhere('nis', $identifier)
            ->orWhere('nip', $identifier)
            ->first();

        if (!$akun) {
            $siswa = Siswa::where('email', $identifier)->orWhere('nis', $identifier)->first();
            if ($siswa) {
                $akun = Akun::where('nis', $siswa->nis)->first();
            }
        }

        if (!$akun) {
            return back()->withErrors([
                'email' => 'Akun dengan identitas tersebut tidak ditemukan dalam sistem.',
            ])->withInput();
        }

        // 3. Tentukan alamat email tujuan pengiriman tautan reset
        $targetEmail = $akun->email;
        if (!$targetEmail && $akun->siswa && $akun->siswa->email) {
            $targetEmail = $akun->siswa->email;
        }
        if (!$targetEmail && filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $targetEmail = $identifier;
        }

        $emailRecord = $targetEmail ?: ($akun->username . '@sinfas.local');

        // 4. Generate token acak kriptografis 64 karakter
        $token = Str::random(64);

        // Bersihkan token lama untuk identitas email ini
        DB::table('password_reset_tokens')->where('email', $emailRecord)->delete();

        // Catat token baru beserta timestamp pembuatan
        DB::table('password_reset_tokens')->insert([
            'email'      => $emailRecord,
            'token'      => $token,
            'created_at' => Carbon::now(),
        ]);

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $emailRecord]);

        // 5. Eksekusi pengiriman email jika alamat email valid terkonfigurasi
        $emailSent = false;
        if ($targetEmail && filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::raw("Halo {$akun->nama},\n\nAnda menerima email ini karena ada permohonan reset password untuk akun SINFAS Anda.\n\nSilakan klik tautan berikut untuk membuat password baru:\n{$resetUrl}\n\nTautan ini akan kedaluwarsa dalam waktu 60 menit.\nJika Anda tidak meminta reset password, abaikan email ini.\n\nSalam,\nTim SINFAS", function ($message) use ($targetEmail, $akun) {
                    $message->to($targetEmail, $akun->nama)
                            ->subject('Permintaan Reset Password - SINFAS');
                });
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim email reset password: " . $e->getMessage());
            }
        }

        Log::info("Password reset request for [{$akun->username}] ({$emailRecord}): {$resetUrl}");

        $statusMessage = 'Permintaan reset password berhasil diproses!';
        if ($emailSent) {
            $statusMessage .= " Tautan reset telah dikirimkan ke email: {$targetEmail}.";
        } else {
            $statusMessage .= " Silakan gunakan tautan verifikasi di bawah ini untuk melanjutkan reset password.";
        }

        return back()->with('status', $statusMessage)->with('direct_reset_url', $resetUrl);
    }

    /**
     * Menampilkan formulir input password baru dengan validasi masa aktif token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        $email = $request->query('email');

        // Validasi keberadaan token di tabel reset
        $resetRecord = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->first();

        if (!$resetRecord) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Tautan reset password ini tidak valid atau sudah digunakan. Silakan ajukan permohonan baru.']);
        }

        // Validasi kedaluwarsa token (maksimal 60 menit)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('token', $token)->delete();
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Tautan reset password telah kedaluwarsa (berlaku 60 menit). Silakan ajukan permohonan baru.']);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email ?: $resetRecord->email,
        ]);
    }

    /**
     * Mengeksekusi perubahan password baru pada database dan menghapus token yang telah terpakai.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request)
    {
        // 1. Validasi parameter token, identitas, dan syarat password baru
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|string',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'token.required'      => 'Token reset password tidak valid.',
            'email.required'      => 'Email atau identitas akun wajib disertakan.',
            'password.required'   => 'Password baru wajib diisi.',
            'password.confirmed'  => 'Konfirmasi password baru tidak cocok.',
        ]);

        // 2. Verifikasi kecocokan token dengan email di tabel password_reset_tokens
        $resetRecord = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors([
                'email' => 'Token reset password tidak valid atau sesi telah berakhir.',
            ])->withInput();
        }

        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('token', $request->token)->delete();
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Tautan reset password telah kedaluwarsa. Silakan ajukan permohonan baru.']);
        }

        // 3. Ambil data akun berdasarkan email atau username
        $identifier = $request->email;
        $usernameCandidate = str_replace('@sinfas.local', '', $identifier);

        $akun = Akun::where('email', $identifier)
            ->orWhere('username', $usernameCandidate)
            ->orWhere('username', $identifier)
            ->first();

        if (!$akun) {
            $siswa = Siswa::where('email', $identifier)->first();
            if ($siswa) {
                $akun = Akun::where('nis', $siswa->nis)->first();
            }
        }

        if (!$akun) {
            return back()->withErrors(['email' => 'Data akun tidak ditemukan.'])->withInput();
        }

        // 4. Update password yang baru dengan enkripsi Bcrypt
        $akun->password = Hash::make($request->password);
        $akun->save();

        // 5. Hapus token agar tidak dapat dipergunakan ulang (one-time use)
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Password berhasil diperbarui! Silakan login menggunakan password baru Anda.');
    }
}
