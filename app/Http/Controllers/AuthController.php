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

class AuthController extends Controller
{
    /**
     * Menampilkan form login
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login dengan proteksi Brute Force (Rate Limiting)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required' => 'Username, Email, atau NIS/NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $identifier = $credentials['email'];
        $throttleKey = $this->throttleKey($request, $identifier);

        // Cek apakah akun terkena rate limiting (terlalu banyak percobaan gagal)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        // Cari akun berdasarkan username, nis, atau nip
        $akun = Akun::where('username', $identifier)
            ->orWhere('nis', $identifier)
            ->orWhere('nip', $identifier)
            ->first();

        if ($akun && Hash::check($credentials['password'], $akun->password)) {
            // Bersihkan limiter jika login berhasil
            RateLimiter::clear($throttleKey);

            Auth::login($akun, $request->filled('remember'));
            $request->session()->regenerate();

            return $this->redirectBasedOnRole($akun)->with('success', 'Selamat datang kembali, ' . $akun->nama . '!');
        }

        // Tambahkan hitungan gagal pada limiter (decay 60 detik)
        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'Akun atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Menampilkan form registrasi
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    /**
     * Proses registrasi akun baru dengan standar validasi password kuat
     */
    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'nis_nip' => 'required|string|max:20',
            'email' => 'required|string|email|max:255',
            'username' => 'required|string|max:255|unique:akun,username',
            'contact_number' => 'nullable|string|max:20',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'nis_nip.required' => 'NIS atau NIP wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $nis_nip = trim($request->nis_nip);
        $role = null;
        $nis = null;
        $nip = null;

        // 1. Cek apakah ada di tabel Siswa
        $siswa = Siswa::where('nis', $nis_nip)->first();
        // 2. Cek apakah ada di tabel Pegawai
        $pegawai = Pegawai::where('nip', $nis_nip)->first();

        if ($siswa) {
            // Cek apakah NIS sudah punya akun
            if (Akun::where('nis', $nis_nip)->exists()) {
                return back()->withErrors(['nis_nip' => 'NIS ini sudah terdaftar memiliki akun.'])->withInput();
            }
            $role = 'siswa';
            $nis = $nis_nip;
        } elseif ($pegawai) {
            // Cek apakah NIP sudah punya akun
            if (Akun::where('nip', $nis_nip)->exists()) {
                return back()->withErrors(['nis_nip' => 'NIP ini sudah terdaftar memiliki akun.'])->withInput();
            }
            $role = 'admin_sarana';
            $nip = $nis_nip;
        } else {
            // NIS atau NIP tidak ditemukan di kedua tabel
            return back()->withErrors([
                'nis_nip' => 'NIS atau NIP tidak terdaftar dalam data sekolah/instansi.',
            ])->withInput();
        }

        // Simpan Akun Baru
        $akun = Akun::create([
            'nis' => $nis,
            'nip' => $nip,
            'nama' => $request->full_name,
            'nomor_kontak' => $request->contact_number,
            'role' => $role,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        // Login otomatis setelah registrasi
        Auth::login($akun);
        $request->session()->regenerate();

        return $this->redirectBasedOnRole($akun)->with('success', 'Registrasi berhasil! Selamat datang di SINFAS.');
    }

    /**
     * Proses logout aman (invalidate session & regenerate token CSRF)
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    /**
     * Helper untuk membuat throttle key login
     */
    protected function throttleKey(Request $request, string $identifier): string
    {
        return Str::transliterate(Str::lower($identifier) . '|' . $request->ip());
    }

    /**
     * Helper untuk redirect halaman dashboard sesuai Role
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
     * Menampilkan form lupa password
     */
    public function showForgotPasswordForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.forgot-password');
    }

    /**
     * Proses pengiriman tautan reset password
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
        ], [
            'email.required' => 'Email, Username, atau NIS/NIP wajib diisi.',
        ]);

        $identifier = trim($request->email);

        // Cari akun berdasarkan email, username, nis, atau nip
        $akun = Akun::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->orWhere('nis', $identifier)
            ->orWhere('nip', $identifier)
            ->first();

        // Jika tidak ketemu langsung di akun, cari di tabel Siswa/Pegawai
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

        // Email target pengiriman
        $targetEmail = $akun->email;
        if (!$targetEmail && $akun->siswa && $akun->siswa->email) {
            $targetEmail = $akun->siswa->email;
        }
        if (!$targetEmail && filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $targetEmail = $identifier;
        }

        // Identifier untuk record token
        $emailRecord = $targetEmail ?: ($akun->username . '@sinfas.local');

        // Buat token unik aman 64 karakter
        $token = Str::random(64);

        // Hapus token lama untuk email ini
        DB::table('password_reset_tokens')->where('email', $emailRecord)->delete();

        // Simpan token baru
        DB::table('password_reset_tokens')->insert([
            'email' => $emailRecord,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $emailRecord]);

        // Coba kirim email jika ada target email yang valid
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
     * Menampilkan formulir input password baru
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        $email = $request->query('email');

        // Validasi keberadaan token di tabel password_reset_tokens
        $resetRecord = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->first();

        if (!$resetRecord) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Tautan reset password ini tidak valid atau sudah digunakan. Silakan ajukan permohonan baru.']);
        }

        // Cek kedaluwarsa token (maksimal 60 menit)
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
     * Proses eksekusi pembaruan password baru
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|string',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'token.required' => 'Token reset password tidak valid.',
            'email.required' => 'Email atau identitas akun wajib disertakan.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

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

        // Cari akun yang cocok
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

        // Update password baru
        $akun->password = Hash::make($request->password);
        $akun->save();

        // Hapus token yang telah berhasil digunakan
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Password berhasil diperbarui! Silakan login menggunakan password baru Anda.');
    }
}


