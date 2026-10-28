<?php

namespace App\Services;

use Exception;

/**
 * Service: TotpService (Time-based One-Time Password — RFC 6238 / RFC 4226)
 *
 * Implementasi murni PHP standar terbuka TOTP yang kompatibel dengan:
 * - Google Authenticator
 * - Microsoft Authenticator
 * - Apple Passwords (iOS / macOS)
 * - Authy, 1Password, Bitwarden
 *
 * Fitur:
 * 1. Pembuatan kunci rahasia acak Base32 aman (kriptografis).
 * 2. Perhitungan token OTP 6 digit berbasis interval waktu 30 detik.
 * 3. Verifikasi token dengan toleransi pergeseran jam (clock drift) ±1 jendela (±30 detik).
 * 4. Pembuatan URI standar otpauth:// dan URL QR Code siap scan.
 * 5. Pembuatan daftar kode darurat (backup recovery codes).
 */
class TotpService
{
    /**
     * Karakter standar Base32 (RFC 3548 / RFC 4648).
     */
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Interval waktu perputaran token dalam detik (standar RFC 6238 adalah 30 detik).
     */
    private const TIME_STEP = 30;

    /**
     * Jumlah digit kode OTP.
     */
    private const DIGITS = 6;

    /**
     * Menghasilkan kunci rahasia (secret key) acak berstandar Base32.
     *
     * @param  int  $length  Panjang string Base32 (default 16 karakter = 80-bit entropy)
     * @return string
     */
    public static function generateSecret(int $length = 16): string
    {
        $secret = '';
        $base32Length = strlen(self::BASE32_CHARS);

        // Ambil byte acak yang aman secara kriptografi
        $randomBytes = random_bytes($length);

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_CHARS[ord($randomBytes[$i]) % $base32Length];
        }

        return $secret;
    }

    /**
     * Menghitung kode OTP 6-digit saat ini berdasarkan secret key.
     *
     * @param  string    $secret     Secret key Base32
     * @param  int|null  $timestamp  Timestamp Unix (opsional, default saat ini)
     * @return string    Kode OTP 6 digit (contoh: "482910")
     */
    public static function calculateOtp(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $timeCounter = (int) floor($timestamp / self::TIME_STEP);

        // Decode string Base32 ke representasi biner
        $secretKeyBytes = self::base32Decode($secret);

        // Bungkus counter waktu ke dalam 8-byte big-endian biner (sesuai RFC 4226)
        $packedCounter = pack('N*', 0) . pack('N*', $timeCounter);

        // Hitung HMAC-SHA1
        $hash = hash_hmac('sha1', $packedCounter, $secretKeyBytes, true);

        // Dynamic Truncation (mengambil 4 bit terakhir byte ke-19 sebagai offset)
        $offset = ord($hash[19]) & 0x0f;

        // Ambil 4 byte mulai dari posisi offset, buang bit tanda (0x7fffffff)
        $binary = ((ord($hash[$offset]) & 0x7f) << 24)
                | ((ord($hash[$offset + 1]) & 0xff) << 16)
                | ((ord($hash[$offset + 2]) & 0xff) << 8)
                | (ord($hash[$offset + 3]) & 0xff);

        // Modulo 10^6 untuk mendapatkan 6 digit
        $otp = $binary % (10 ** self::DIGITS);

        // Format padding 0 di depan jika panjang kurang dari 6 digit
        return str_pad((string) $otp, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Memvalidasi apakah kode OTP yang dimasukkan pengguna cocok dengan kunci rahasia.
     * Mendukung toleransi ±1 jendela (±30 detik) untuk mengatasi selisih waktu antara HP dan server.
     *
     * @param  string  $secret       Kunci rahasia Base32
     * @param  string  $code         Kode 6-digit inputan pengguna
     * @param  int     $windowSteps  Jumlah langkah toleransi (default: 1 langkah sebelum & sesudah)
     * @return bool
     */
    public static function verify(string $secret, string $code, int $windowSteps = 1): bool
    {
        $code = trim(str_replace(' ', '', $code));

        if (strlen($code) !== self::DIGITS || ! ctype_digit($code)) {
            return false;
        }

        $currentTime = time();

        for ($i = -$windowSteps; $i <= $windowSteps; $i++) {
            $calculatedOtp = self::calculateOtp($secret, $currentTime + ($i * self::TIME_STEP));
            // Gunakan hash_equals untuk mencegah timing attack
            if (hash_equals($calculatedOtp, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Menghasilkan URI standar `otpauth://totp/...` untuk pembacaan aplikasi Authenticator.
     *
     * @param  string  $label   Identitas akun pengguna (misal: "admin@smkn11bdg.sch.id")
     * @param  string  $secret  Kunci rahasia Base32
     * @param  string  $issuer  Nama aplikasi (default: "SINFAS")
     * @return string
     */
    public static function getOtpAuthUrl(string $label, string $secret, string $issuer = 'SINFAS'): string
    {
        $encodedIssuer = rawurlencode($issuer);
        $encodedLabel = rawurlencode($label);

        return "otpauth://totp/{$encodedIssuer}:{$encodedLabel}?secret={$secret}&issuer={$encodedIssuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Menghasilkan URL gambar QR Code siap render untuk di-scan oleh aplikasi Authenticator.
     * Menggunakan API render QR standar yang andal dan ringan.
     *
     * @param  string  $otpAuthUrl  URI otpauth://
     * @param  int     $size        Ukuran dimensi QR code dalam piksel
     * @return string  URL gambar QR code
     */
    public static function getQrCodeImageUrl(string $otpAuthUrl, int $size = 200): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&margin=10&data=' . rawurlencode($otpAuthUrl);
    }

    /**
     * Menghasilkan daftar kode pemulihan darurat acak (Emergency Recovery Codes).
     * Contoh output: ["A8K3-92M1", "P9X2-74T0", ...]
     *
     * @param  int  $count  Jumlah kode yang dihasilkan (default 8)
     * @return array<int, string>
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        // Karakter yang mudah dibaca tanpa ambigu (menghilangkan I, O, 0, 1)
        $charset = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $charsetLength = strlen($charset);

        for ($i = 0; $i < $count; $i++) {
            $bytes = random_bytes(8);
            $part1 = '';
            $part2 = '';

            for ($j = 0; $j < 4; $j++) {
                $part1 .= $charset[ord($bytes[$j]) % $charsetLength];
                $part2 .= $charset[ord($bytes[$j + 4]) % $charsetLength];
            }

            $codes[] = "{$part1}-{$part2}";
        }

        return $codes;
    }

    /**
     * Melakukan decode dari string Base32 ke biner mentah.
     *
     * @param  string  $base32String
     * @return string
     * @throws Exception
     */
    private static function base32Decode(string $base32String): string
    {
        $base32String = strtoupper(trim($base32String));
        $buffer = 0;
        $bitsLeft = 0;
        $binary = '';

        for ($i = 0; $i < strlen($base32String); $i++) {
            $char = $base32String[$i];
            $val = strpos(self::BASE32_CHARS, $char);

            if ($val === false) {
                continue; // Abaikan karakter padding atau spasi
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $binary .= chr(($buffer >> $bitsLeft) & 0xff);
            }
        }

        return $binary;
    }
}
