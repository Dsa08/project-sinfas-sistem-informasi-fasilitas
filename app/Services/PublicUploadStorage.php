<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicUploadStorage
{
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

    public function url(?string $storedPath): ?string
    {
        if (!$storedPath) {
            return null;
        }

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

        // Preserve avatars uploaded before the public_html disk was introduced.
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return Storage::disk('public_uploads')->url($path);
    }

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

        // Remove files created by older versions under the project's public folder.
        $legacyPath = public_path($path);
        if (is_file($legacyPath)) {
            @unlink($legacyPath);
        }
    }

    private function safeRelativePath(string $path): ?string
    {
        $path = trim($path, '/');
        if ($path === '' || in_array('..', explode('/', $path), true)) {
            return null;
        }

        return $path;
    }
}
