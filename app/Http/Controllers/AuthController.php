<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Pegawai;
use App\Models\Siswa;
use App\Support\RoleHome;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Controller AuthController
 *
 * Mengelola seluruh alur otentikasi dan keamanan akun pengguna:
 * 1. Login multi-identitas (Username / NIS / NIP) dengan proteksi Brute Force (Rate Limiting).
 * 3. Pemulihan kata sandi (Forgot Password) via token kriptografis dan tautan email.
 * 4. Pembaruan kata sandi baru (Reset Password) dengan verifikasi masa aktif token (60 menit).
 * 5. Logout aman dengan invalidasi session dan regenerasi token CSRF.
 */
class AuthController extends Controller
{
    /**
     * Menampilkan antarmuka formulir masuk (login) aplikasi.
     * Jika pengguna sudah dalam sesi terautentikasi, dialihkan otomatis ke halaman awal sesuai perannya.
     *
     * @return View|RedirectResponse Tampilan view 'auth.login' atau pengalihan rute ke beranda peran pengguna.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Memproses otentikasi login pengguna dengan proteksi pembatasan laju percobaan (Rate Limiting).
     * Mendukung multi-identitas: Username, Email resmi, NIS siswa, atau NIP pegawai/staf sarana.
     *
     * Prasyarat:
     * - Input identifier (email/username/NIS/NIP) dan password wajib diisi.
     *
     * Efek Samping:
     * - Menghitung jumlah kegagalan login pada Rate Limiter berbasis kombinasi identitas dan IP klien.
     * - Menginisialisasi sesi login baru (`Auth::login`) dan meregenerasi ID sesi CSRF untuk mencegah session fixation.
     *
     * @param Request $request Request HTTP yang memuat kredensial login (identitas dan password).
     * @return RedirectResponse Pengalihan ke beranda peran pengguna jika sukses, atau kembali ke formulir dengan pesan error.
     */
    public function login(Request $request)
    {
        // 1. Validasi keberadaan input identifier dan password
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required' => 'Username, Email, atau NIS/NIP wajib diisi.',
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

        // 3. Cari akun berdasarkan salah satu identitas unik (username, email, nis pada siswa, atau nip pada pegawai/staff)
        $akun = Akun::where(function ($query) use ($identifier) {
            $query->where('username', $identifier)
                ->orWhere('email', $identifier)
                ->orWhereHas('siswa', fn ($q) => $q->where('nis', $identifier));
        })->first();

        if (! $akun) {
            $nipAccounts = Akun::where(function ($query) use ($identifier) {
                $query->whereHas('pegawai', fn ($q) => $q->where('nip', $identifier))
                    ->orWhereHas('staffSarana', fn ($q) => $q->where('nip', $identifier));
            })->where('is_active', true)->limit(2)->get();

            if ($nipAccounts->count() > 1) {
                return back()->withErrors([
                    'email' => 'NIP ini memiliki lebih dari satu akun. Silakan masuk menggunakan username akun.',
                ])->onlyInput('email');
            }
            $akun = $nipAccounts->first();
        }

        // 4. Verifikasi kecocokan hash kata sandi dan status keaktifan akun
        if ($akun && Hash::check($credentials['password'], $akun->password)) {
            // Cek apakah akun dinonaktifkan oleh administrator
            if (isset($akun->is_active) && ! $akun->is_active) {
                return back()->withErrors([
                    'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator Sistem.',
                ])->onlyInput('email');
            }

            // Bersihkan riwayat percobaan gagal pada Rate Limiter setelah login berhasil
            RateLimiter::clear($throttleKey);

            // Simpan autentikasi pada session web Laravel (dengan opsi Remember Me)
            Auth::login($akun, $request->filled('remember'));
            $request->session()->regenerate();

            return $this->redirectBasedOnRole($akun)->with('success', 'Selamat datang kembali, '.$akun->nama.'!');
        }

        // 5. Catat kegagalan login ke Rate Limiter (decay time 60 detik) untuk menghitung ambang batas brute-force
        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'Akun atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Memproses keluar sistem (logout) pengguna secara aman.
     *
     * Efek Samping:
     * - Menghapus sesi otentikasi aktif (`Auth::logout`).
     * - Membatalkan (invalidate) token session peramban dan meregenerasi token CSRF baru.
     *
     * @param Request $request Request HTTP pemanggil logout.
     * @return RedirectResponse Pengalihan rute kembali ke halaman login dengan flash message sukses.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    /**
     * Menghasilkan kunci pembatas laju (rate limiter key) berbasis kombinasi identifier pengguna dan IP klien.
     *
     * @param Request $request Request HTTP dari klien.
     * @param string $identifier String identitas yang diinputkan pengguna (username/email/NIS/NIP).
     * @return string Kunci unik untuk pencatatan frekuensi percobaan login pada cache rate limiter.
     */
    protected function throttleKey(Request $request, string $identifier): string
    {
        return Str::transliterate(Str::lower($identifier).'|'.$request->ip());
    }

    /**
     * Menentukan dan mengarahkan rute tujuan pengguna sesuai dengan peran (role) akun yang dimiliki.
     *
     * @param Akun $user Model Akun pengguna yang terautentikasi.
     * @return RedirectResponse Pengalihan ke URL beranda yang sesuai dengan peran pengguna.
     */
    private function redirectBasedOnRole($user)
    {
        return redirect()->to(RoleHome::url($user));
    }

    /**
     * Menampilkan formulir permohonan pemulihan kata sandi (lupa password).
     *
     * @return View|RedirectResponse Tampilan view 'auth.forgot-password' atau redirect jika sudah login.
     */
    public function showForgotPasswordForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.forgot-password');
    }

    /**
     * Memproses permohonan reset sandi, menerbitkan token kriptografis 64-karakter, dan mengirim tautan via surel.
     *
     * Efek Samping:
     * - Menyimpan hash token reset ke tabel `password_reset_tokens`.
     * - Mengirimkan surel berisi URL pemulihan jika alamat surel terdaftar dan valid.
     *
     * @param Request $request Request HTTP yang memuat identitas akun pemohon reset sandi.
     * @return RedirectResponse Kembali ke halaman sebelumnya dengan pesan status tergeneralisasi untuk privasi.
     */
    public function sendResetLink(Request $request)
    {
        $genericStatusMessage = 'Jika akun aktif memiliki email terdaftar, tautan pemulihan akan dikirim ke email tersebut.';

        // 1. Validasi input identifier
        $request->validate([
            'email' => 'required|string',
        ], [
            'email.required' => 'Email, Username, atau NIS/NIP wajib diisi.',
        ]);

        $identifier = trim($request->email);

        // 2. Pencarian akun pengguna melalui tabel akun maupun master siswa/pegawai (ERD 2)
        $akun = Akun::where(function ($query) use ($identifier) {
            $query->where('email', $identifier)
                ->orWhere('username', $identifier)
                ->orWhereHas('siswa', fn ($q) => $q->where('nis', $identifier));
        })->first();

        if (! $akun) {
            $nipAccounts = Akun::where(function ($query) use ($identifier) {
                $query->whereHas('pegawai', fn ($q) => $q->where('nip', $identifier))
                    ->orWhereHas('staffSarana', fn ($q) => $q->where('nip', $identifier));
            })->where('is_active', true)->limit(2)->get();

            if ($nipAccounts->count() > 1) {
                return back()->with('status', $genericStatusMessage);
            }
            $akun = $nipAccounts->first();
        }

        if (! $akun) {
            $siswa = Siswa::where('email', $identifier)->orWhere('nis', $identifier)->first();
            if ($siswa) {
                $akun = $siswa->akun;
            }
            if (! $akun) {
                $pegawai = Pegawai::where('email', $identifier)->first();
                $akun = $pegawai?->akun;
            }
        }

        if (! $akun || ! $akun->is_active) {
            return back()->with('status', $genericStatusMessage);
        }

        // 3. Tentukan alamat email tujuan pengiriman tautan reset
        $targetEmail = $akun->email;
        $profile = $akun->siswa ?? $akun->pegawai;
        if (! $targetEmail && $profile?->email) {
            $targetEmail = $profile->email;
        }
        if (! $targetEmail && filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $targetEmail = $identifier;
        }

        // Reset wajib membuktikan akses ke kotak email; URL pemulihan tidak boleh muncul di halaman atau log.
        if (! $targetEmail || ! filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            return back()->with('status', $genericStatusMessage);
        }

        $emailRecord = $targetEmail;

        // 4. Generate token acak kriptografis 64 karakter
        $token = Str::random(64);

        // Bersihkan token lama untuk identitas email ini
        DB::table('password_reset_tokens')->where('email', $emailRecord)->delete();

        // Simpan hash token agar kebocoran tabel tidak langsung membocorkan tautan reset.
        DB::table('password_reset_tokens')->insert([
            'email' => $emailRecord,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $emailRecord]);

        // 5. Eksekusi pengiriman email jika alamat email valid terkonfigurasi
        try {
            Mail::raw("Halo {$akun->nama},\n\nAnda menerima email ini karena ada permintaan untuk mengatur ulang kata sandi akun SINFAS.\n\nBuka tautan berikut untuk membuat kata sandi baru:\n{$resetUrl}\n\nTautan ini berlaku selama 60 menit. Jika Anda tidak meminta perubahan ini, abaikan email ini.\n\nSalam,\nTim SINFAS", function ($message) use ($targetEmail, $akun) {
                $message->to($targetEmail, $akun->nama)
                    ->subject('Permintaan Atur Ulang Kata Sandi - SINFAS');
            });
        } catch (\Throwable $e) {
            DB::table('password_reset_tokens')->where('email', $emailRecord)->delete();
            // Hindari isi exception karena transport email mungkin menyertakan isi pesan.
            Log::warning('Gagal mengirim email reset password.', ['exception_class' => $e::class]);
        }

        // Samakan respons agar halaman tidak membocorkan keberadaan akun atau email.
        return back()->with('status', $genericStatusMessage);
    }

    /**
     * Menampilkan antarmuka formulir pembuatan kata sandi baru berdasarkan token kriptografis.
     * Memeriksa keberadaan token di basis data dan masa berlakunya (kedaluwarsa setelah 60 menit).
     *
     * Prasyarat:
     * - Parameter token dan email harus valid dan cocok dengan rekaman di tabel `password_reset_tokens`.
     * - Waktu pembuatan token tidak boleh lebih dari 60 menit yang lalu.
     *
     * @param Request $request Request HTTP yang memuat query parameter email pemohon.
     * @param string $token Token acak 64-karakter dari tautan yang diklik pada email.
     * @return View|RedirectResponse Tampilan view 'auth.reset-password' atau redirect ke request token jika token tidak valid/kedaluwarsa.
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        $email = $request->query('email');

        // Validasi keberadaan token di tabel reset
        $resetRecord = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $resetRecord || ! Hash::check($token, $resetRecord->token)) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Tautan untuk mengatur ulang kata sandi sudah tidak berlaku. Silakan ajukan permintaan baru.']);
        }

        // Validasi kedaluwarsa token (maksimal 60 menit)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $resetRecord->email)->delete();

            return redirect()->route('password.request')
                ->withErrors(['email' => 'Tautan untuk mengatur ulang kata sandi sudah kedaluwarsa. Silakan ajukan permintaan baru.']);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email ?: $resetRecord->email,
        ]);
    }

    /**
     * Memvalidasi dan mengeksekusi pembaruan kata sandi baru ke basis data serta menganulir token reset yang telah dipakai.
     *
     * Prasyarat:
     * - Kata sandi baru minimal 8 karakter, wajib kombinasi huruf dan angka, serta cocok dengan konfirmasi sandi.
     *
     * Efek Samping:
     * - Mengubah hash kata sandi akun di tabel `akun`.
     * - Menghapus baris token dari tabel `password_reset_tokens` agar token bersifat satu kali pakai (one-time use).
     *
     * @param Request $request Request HTTP yang memuat token, email, sandi baru, dan konfirmasi sandi.
     * @return RedirectResponse Pengalihan ke halaman login dengan pesan sukses pembaruan sandi.
     */
    public function resetPassword(Request $request)
    {
        // 1. Validasi parameter token, identitas, dan syarat password baru
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|string',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'token.required' => 'Tautan untuk mengatur ulang kata sandi tidak dapat digunakan. Silakan ajukan permintaan baru.',
            'email.required' => 'Email atau identitas akun wajib disertakan.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // 2. Verifikasi kecocokan token dengan email di tabel password_reset_tokens
        $resetRecord = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (! $resetRecord || ! Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors([
                'email' => 'Tautan untuk mengatur ulang kata sandi tidak dapat digunakan. Silakan ajukan permintaan baru.',
            ])->withInput();
        }

        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $resetRecord->email)->delete();

            return redirect()->route('password.request')
                ->withErrors(['email' => 'Tautan untuk mengatur ulang kata sandi sudah kedaluwarsa. Silakan ajukan permintaan baru.']);
        }

        // 3. Ambil data akun berdasarkan email atau username
        $identifier = $request->email;
        $usernameCandidate = str_replace('@sinfas.local', '', $identifier);

        $akun = Akun::where('email', $identifier)
            ->orWhere('username', $usernameCandidate)
            ->orWhere('username', $identifier)
            ->first();

        if (! $akun) {
            $siswa = Siswa::where('email', $identifier)->first();
            if ($siswa) {
                $akun = $siswa->akun;
            }
            if (! $akun) {
                $pegawai = Pegawai::where('email', $identifier)->first();
                $akun = $pegawai?->akun;
            }
        }

        if (! $akun) {
            return back()->withErrors(['email' => 'Data akun tidak ditemukan.'])->withInput();
        }

        // 4. Update password yang baru dengan enkripsi Bcrypt
        $akun->password = Hash::make($request->password);
        $akun->save();

        // 5. Hapus token agar tidak dapat dipergunakan ulang (one-time use)
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru Anda.');
    }
}
