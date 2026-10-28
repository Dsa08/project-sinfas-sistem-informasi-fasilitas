<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Services\LoginAuditService;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Controller: TwoFactorController
 *
 * Mengelola seluruh alur Two-Factor Authentication (2FA TOTP):
 * 1. Tantangan Login (Login Challenge) untuk akun administrator yang telah mengaktifkan 2FA.
 * 2. Setup & Aktivasi (QR Code + Kunci Manual + Konfirmasi Token 6 Digit).
 * 3. Manajemen Kode Pemulihan Darurat (Recovery Codes).
 * 4. Penonaktifan 2FA dengan verifikasi kata sandi saat ini.
 */
class TwoFactorController extends Controller
{
    /**
     * Menampilkan halaman tantangan 2FA saat proses login berlangsung.
     */
    public function showChallenge(Request $request)
    {
        $userId = $request->session()->get('login.2fa.id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = Akun::find($userId);
        if (! $user) {
            $request->session()->forget(['login.2fa.id', 'login.2fa.remember']);
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge', [
            'user' => $user,
        ]);
    }

    /**
     * Memvalidasi kode 2FA (TOTP atau Recovery Code) saat proses login.
     */
    public function verifyChallenge(Request $request)
    {
        $userId = $request->session()->get('login.2fa.id');
        $remember = (bool) $request->session()->get('login.2fa.remember', false);

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = Akun::find($userId);
        if (! $user || ! $user->hasTwoFactorEnabled()) {
            $request->session()->forget(['login.2fa.id', 'login.2fa.remember']);
            return redirect()->route('login');
        }

        // Terapkan rate limiting pada input 2FA (maksimal 5x percobaan per menit)
        $throttleKey = '2fa|'.$user->id_akun.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            LoginAuditService::record(
                $request,
                'terkunci_rate_limit',
                $user,
                $user->username,
                "Pemblokiran brute-force 2FA: Tunggu {$seconds} detik"
            );

            return back()->withErrors([
                'code' => "Terlalu banyak percobaan kode verifikasi. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $code = trim($request->input('code') ?? '');
        $recoveryCode = strtoupper(trim($request->input('recovery_code') ?? ''));

        $isValid = false;
        $usedRecoveryCode = false;

        // 1. Validasi via kode OTP 6-digit (Aplikasi Authenticator)
        if (! empty($code)) {
            $isValid = TotpService::verify($user->two_factor_secret, $code);
        }
        // 2. Validasi via kode pemulihan darurat (Emergency Recovery Code)
        elseif (! empty($recoveryCode)) {
            $codes = $user->two_factor_recovery_codes ?? [];
            if (in_array($recoveryCode, $codes, true)) {
                $isValid = true;
                $usedRecoveryCode = true;

                // Konsumsi kode pemulihan tersebut agar tidak dapat digunakan kembali
                $remainingCodes = array_values(array_diff($codes, [$recoveryCode]));
                $user->two_factor_recovery_codes = $remainingCodes;
                $user->save();
            }
        }

        if ($isValid) {
            // Bersihkan batasan rate limit
            RateLimiter::clear($throttleKey);

            // Bersihkan sesi sementara 2FA
            $request->session()->forget(['login.2fa.id', 'login.2fa.remember']);

            // Login akun secara resmi ke sesi web
            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Single Session Lock: Simpan session ID terbaru
            $user->forceFill([
                'current_session_id' => $request->session()->getId(),
            ])->save();

            // Audit Log: Autentikasi 2FA Sukses
            $methodNote = $usedRecoveryCode ? 'Verifikasi Berhasil via Kode Darurat (Recovery Code)' : 'Verifikasi Berhasil via Google Authenticator (TOTP)';
            LoginAuditService::record(
                $request,
                'sukses',
                $user,
                $user->username,
                "Login 2FA Berhasil: {$methodNote}"
            );

            // Redirect sesuai peran administrator
            if ($user->isAdminSistem()) {
                return redirect()->route('admin.sistem.dashboard')->with('success', 'Selamat datang kembali, '.$user->nama.'!');
            }

            return redirect()->route('admin.dashboard')->with('success', 'Selamat datang kembali, '.$user->nama.'!');
        }

        // Jika kode tidak cocok, catat kegagalan
        RateLimiter::hit($throttleKey, 60);

        LoginAuditService::record(
            $request,
            'gagal_password',
            $user,
            $user->username,
            'Kode 2FA tidak valid atau sudah kadaluarsa'
        );

        return back()->withErrors([
            'code' => 'Kode verifikasi yang Anda masukkan tidak valid atau telah kadaluarsa.',
        ]);
    }

    /**
     * Menginisialisasi atau menampilkan status pengaturan 2FA pada profil admin.
     */
    public function setup(Request $request)
    {
        $user = Auth::user();

        // Jika 2FA sudah aktif, tampilkan status terpasang beserta opsi kelola kode pemulihan
        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'enabled' => true,
                'confirmed_at' => $user->two_factor_confirmed_at?->translatedFormat('d F Y H:i'),
                'remaining_recovery_codes' => count($user->two_factor_recovery_codes ?? []),
            ]);
        }

        // Jika belum aktif, siapkan draft secret key Base32
        $draftSecret = $request->session()->get('2fa.draft_secret');
        if (! $draftSecret) {
            $draftSecret = TotpService::generateSecret();
            $request->session()->put('2fa.draft_secret', $draftSecret);
        }

        $label = $user->email ?? $user->username;
        $otpAuthUrl = TotpService::getOtpAuthUrl($label, $draftSecret, 'SINFAS');
        $qrCodeUrl = TotpService::getQrCodeImageUrl($otpAuthUrl);

        return response()->json([
            'enabled' => false,
            'secret' => $draftSecret,
            'secret_formatted' => implode(' ', str_split($draftSecret, 4)),
            'qr_url' => $qrCodeUrl,
            'otpauth' => $otpAuthUrl,
        ]);
    }

    /**
     * Mengonfirmasi aktivasi 2FA dengan memverifikasi kode 6 digit pertama dari aplikasi Authenticator.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'Masukkan 6 digit kode dari aplikasi Authenticator.',
        ]);

        $user = Auth::user();
        $draftSecret = $request->session()->get('2fa.draft_secret');

        if (! $draftSecret) {
            return back()->withErrors(['code' => 'Sesi penyiapan 2FA telah berakhir. Silakan muat ulang halaman.']);
        }

        if (! TotpService::verify($draftSecret, $request->code)) {
            return back()->withErrors(['code' => 'Kode verifikasi tidak sesuai. Pastikan jam pada perangkat Anda akurat.']);
        }

        // Hasilkan 8 kode pemulihan darurat
        $recoveryCodes = TotpService::generateRecoveryCodes(8);

        // Simpan kunci rahasia dan kode darurat ke database (otomatis terenkripsi AES-256)
        $user->two_factor_secret = $draftSecret;
        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->two_factor_confirmed_at = now();
        $user->save();

        $request->session()->forget('2fa.draft_secret');

        // Simpan recovery codes di flash session agar dapat disalin / diunduh pengguna
        return back()->with([
            'success' => 'Autentikasi Dua Faktor (2FA) berhasil diaktifkan!',
            'show_recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Menonaktifkan 2FA dengan verifikasi kata sandi administrator.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
        ], [
            'current_password.required' => 'Masukkan kata sandi akun Anda untuk menonaktifkan 2FA.',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak sesuai.']);
        }

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return back()->with('success', 'Autentikasi Dua Faktor (2FA) telah berhasil dinonaktifkan.');
    }
}
