<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Models\Akun;
use App\Models\Siswa;
use App\Models\Pegawai;

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
}


