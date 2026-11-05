<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service PublicUploadStorage
 *
 * Mengelola abstraksi penyimpanan, pembacaan URL, dan penghapusan berkas publik (foto sarana,
 * bukti fisik pengembalian, dan foto profil pengguna) pada disk `public_uploads`.
 * Menjaga kompatibilitas struktur path antara lingkungan local development dan shared hosting cPanel.
 */
class PublicUploadStorage
{
    /**
     * Menyimpan berkas unggahan ke sub-direktori disk public uploads dengan penamaan aman.
     *
     * Prasyarat:
     * - Objek berkas `$file` harus valid dan tidak mengalami korupsi saat pengunggahan HTTP.
     *
     * Efek Samping:
     * - Menulis berkas fisik baru ke dalam disk penyimpanan server.
     *
     * @param UploadedFile $file Berkas unggahan yang diterima dari HTTP request.
     * @param string $directory Nama sub-direktori tujuan (misal: 'items', 'returns', 'avatars').
     * @param string|null $filename Nama berkas kustom opsional. Jika null, UUID v4 otomatis digunakan.
     * @return string Path relatif standar yang diawali dengan 'uploads/' untuk disimpan di database.
     * @throws \RuntimeException Jika penulisan berkas ke disk penyimpanan gagal.
     */
    public function storeFile(UploadedFile $file, string $directory, ?string $filename = null): string
    {
        $directory = trim($directory, '/\\');
        $filename ??= Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
        $storedPath = Storage::disk('public_uploads')->putFileAs($directory, $file, $filename);

        if (!$storedPath) {
            throw new \RuntimeException('Berkas gagal disimpan ke direktori uploads publik.');
        }

        return 'uploads/' . str_replace('\\', '/', $storedPath);
    }

    /**
     * Mengonversi path penyimpanan database menjadi URL publik yang dapat diakses oleh browser klien.
     * Mendukung URL eksternal langsung, path legacy 'storage/', path 'uploads/', dan fallback lokal.
     *
     * @param string|null $storedPath String path yang tersimpan di kolom basis data.
     * @return string|null URL publik lengkap, atau null jika path input kosong.
     */
    public function url(?string $storedPath): ?string
    {
        if (!$storedPath) {
            return null;
        }

        // Kembalikan langsung jika path sudah berupa URL absolut eksternal (misal CDN / placeholder)
        if (filter_var($storedPath, FILTER_VALIDATE_URL)) {
            return $storedPath;
        }

        $path = ltrim(str_replace('\\', '/', $storedPath), '/');
        if (str_starts_with($path, 'uploads/')) {
            return Storage::disk('public_uploads')->url(substr($path, strlen('uploads/')));
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        if (is_file(public_path($path))) {
            return asset($path);
        }

        if (Storage::disk('public_uploads')->exists($path)) {
            return Storage::disk('public_uploads')->url($path);
        }

        // Mendukung berkas avatar lama sebelum disk public_uploads diperkenalkan
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return Storage::disk('public_uploads')->url($path);
    }

    /**
     * Menghapus berkas fisik dari disk penyimpanan dengan sanitasi keamanan direktori.
     *
     * Efek Samping:
     * - Menghapus file fisik di disk `public_uploads` atau folder legacy.
     *
     * @param string|null $storedPath Path berkas yang akan dihapus dari server.
     * @return void
     */
    public function delete(?string $storedPath): void
    {
        if (!$storedPath || filter_var($storedPath, FILTER_VALIDATE_URL)) {
            return;
        }

        $path = ltrim(str_replace('\\', '/', $storedPath), '/');
        $relativePath = preg_replace('#^(uploads|storage)/#', '', $path);
        $relativePath = $this->safeRelativePath($relativePath);
        if ($relativePath === null) {
            return;
        }

        Storage::disk('public_uploads')->delete($relativePath);
        Storage::disk('public')->delete($relativePath);

        // Menghapus berkas versi lama yang berada langsung di bawah folder public aplikasi
        $legacyPath = public_path($path);
        if (is_file($legacyPath)) {
            @unlink($legacyPath);
        }
    }

    /**
     * Memvalidasi path relatif untuk mencegah celah keamanan Directory Traversal (eksploitasi '../').
     *
     * @param string $path Path relatif yang akan diperiksa keamanannya.
     * @return string|null Path yang aman, atau null jika terdeteksi manipulasi traversal '../'.
     */
    private function safeRelativePath(string $path): ?string
    {
        $path = trim($path, '/');
        if ($path === '' || in_array('..', explode('/', $path), true)) {
            return null;
        }

        return $path;
    }
}
