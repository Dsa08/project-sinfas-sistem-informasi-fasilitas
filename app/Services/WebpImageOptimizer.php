<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service WebpImageOptimizer
 *
 * Mengoptimalkan ukuran dan performa berkas gambar yang diunggah pengguna (foto sarana prasarana,
 * bukti fisik pengembalian, dan avatar pengguna) dengan melakukan kompresi otomatis ke format WebP.
 * Melakukan pengecilan skala (resizing) proporsional jika dimensi gambar melebihi 1920 piksel,
 * serta menyediakan fallback mulus ke format asli (JPG/PNG) jika modul GD WebP di server belum aktif.
 */
class WebpImageOptimizer
{
    /**
     * Batas resolusi dimensi maksimum (lebar atau tinggi) dalam piksel untuk mencegah beban memori berlebih.
     */
    private const MAX_DIMENSION = 1920;

    /**
     * Kualitas kompresi WebP (0-100) yang memberikan rasio ukuran berkas kecil dengan ketajaman visual optimal.
     */
    private const QUALITY = 82;

    /**
     * Menyimpan berkas gambar unggahan ke disk penyimpanan publik Laravel dengan prioritas konversi ke format WebP.
     *
     * Efek Samping:
     * - Membuat sub-direktori pada disk penyimpanan jika belum ada.
     * - Menyimpan berkas gambar hasil kompresi (atau berkas asli jika konversi gagal) ke disk target.
     *
     * @param UploadedFile $file Berkas gambar yang diunggah melalui formulir HTTP.
     * @param string $directory Direktori penyimpanan di dalam disk target (misal: 'items', 'returns').
     * @param string $disk Nama disk penyimpanan yang dikonfigurasi pada filesystems (default: 'public_uploads').
     * @param string $prefix Awalan nama file untuk mempermudah identifikasi (misal: 'item', 'avatar').
     * @return string Path relatif berkas yang tersimpan untuk dicatat pada basis data.
     */
    public function storeOnDisk(UploadedFile $file, string $directory, string $disk = 'public_uploads', string $prefix = 'image'): string
    {
        Storage::disk($disk)->makeDirectory($directory);

        $relativePath = trim($directory, '/') . '/' . $prefix . '_' . Str::uuid() . '.webp';
        $destination = Storage::disk($disk)->path($relativePath);

        // Coba kompresi gambar ke WebP terlebih dahulu untuk efisiensi penyimpanan
        if ($this->convert($file, $destination)) {
            return $relativePath;
        }

        // Fallback: simpan berkas dengan format dan ekstensi aslinya jika fungsi WebP tidak tersedia di server
        Log::warning('Konversi WebP tidak tersedia di server; menyimpan berkas gambar dalam format aslinya.', [
            'mime' => $file->getMimeType(),
        ]);

        return $file->storeAs($directory, $prefix . '_' . Str::uuid() . '.' . $this->originalImageExtension($file), $disk);
    }

    /**
     * Menyimpan berkas gambar langsung ke path direktori fisik filesystem lokal dengan konversi WebP.
     *
     * Efek Samping:
     * - Membuat direktori tujuan jika belum ditemukan di server.
     * - Menulis berkas gambar WebP (atau memindahkan file asli sebagai fallback) ke sistem berkas.
     *
     * @param UploadedFile $file Berkas gambar yang diunggah.
     * @param string $destinationPath Path absolut direktori tujuan di server.
     * @param string $prefix Awalan nama berkas.
     * @return string Nama berkas fisik yang berhasil disimpan.
     */
    public function storeAt(UploadedFile $file, string $destinationPath, string $prefix = 'image'): string
    {
        if (!is_dir($destinationPath)) {
            @mkdir($destinationPath, 0755, true);
        }

        $filename = $prefix . '_' . time() . '_' . Str::random(8) . '.webp';
        $destination = rtrim($destinationPath, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if ($this->convert($file, $destination)) {
            return $filename;
        }

        // Pindahkan file asli secara langsung jika kompresi WebP gagal
        $fallbackName = $prefix . '_' . time() . '_' . Str::random(8) . '.' . $this->originalImageExtension($file);
        $file->move($destinationPath, $fallbackName);

        return $fallbackName;
    }

    /**
     * Memproses pengecilan skala proporsional dan mengonversi format gambar ke WebP menggunakan pustaka GD PHP.
     *
     * Prasyarat:
     * - Ekstensi PHP GD dengan fungsi `imagewebp` dan `imagecreatefromstring` harus terpasang di runtime server.
     *
     * @param UploadedFile $file Berkas sumber gambar.
     * @param string $destination Path absolut tempat berkas keluaran WebP akan ditulis.
     * @return bool True jika kompresi dan penyimpanan WebP berhasil, atau false jika modul GD tidak didukung.
     */
    private function convert(UploadedFile $file, string $destination): bool
    {
        if (!function_exists('imagewebp') || !function_exists('imagecreatefromstring')) {
            return false;
        }

        $imageData = @file_get_contents($file->getRealPath());
        if ($imageData === false) {
            return false;
        }

        $source = @imagecreatefromstring($imageData);
        unset($imageData);

        if ($source === false) {
            return false;
        }

        // Hitung skala baru agar dimensi gambar tidak melebihi batas MAX_DIMENSION (1920px)
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, self::MAX_DIMENSION / max($sourceWidth, $sourceHeight));
        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));
        $output = imagecreatetruecolor($width, $height);

        if ($output === false) {
            imagedestroy($source);
            return false;
        }

        // Pertahankan transparansi kanal Alpha untuk gambar PNG atau WebP berlatar belakang transparan
        imagealphablending($output, false);
        imagesavealpha($output, true);
        $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
        imagefilledrectangle($output, 0, 0, $width, $height, $transparent);
        imagecopyresampled($output, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        $saved = @imagewebp($output, $destination, self::QUALITY);
        imagedestroy($output);
        imagedestroy($source);

        if (!$saved && file_exists($destination)) {
            @unlink($destination);
        }

        return $saved;
    }

    /**
     * Menentukan ekstensi berkas fallback yang aman berdasarkan MIME type gambar yang sebenarnya.
     *
     * @param UploadedFile $file Berkas unggahan yang akan dicek tipe MIME-nya.
     * @return string Ekstensi berkas yang sesuai ('jpg', 'png', 'webp', dsb.).
     */
    private function originalImageExtension(UploadedFile $file): string
    {
        return match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => strtolower($file->getClientOriginalExtension() ?: 'img'),
        };
    }
}
