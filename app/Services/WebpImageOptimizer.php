<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebpImageOptimizer
{
    private const MAX_DIMENSION = 1920;
    private const QUALITY = 82;

    /**
     * Store an uploaded image on the configured public uploads disk, preferring WebP.
     */
    public function storeOnDisk(UploadedFile $file, string $directory, string $disk = 'public_uploads', string $prefix = 'image'): string
    {
        Storage::disk($disk)->makeDirectory($directory);

        $relativePath = trim($directory, '/') . '/' . $prefix . '_' . Str::uuid() . '.webp';
        $destination = Storage::disk($disk)->path($relativePath);

        if ($this->convert($file, $destination)) {
            return $relativePath;
        }

        Log::warning('WebP conversion unavailable; storing uploaded image in its original format.', [
            'mime' => $file->getMimeType(),
        ]);

        return $file->storeAs($directory, $prefix . '_' . Str::uuid() . '.' . $this->originalImageExtension($file), $disk);
    }

    /**
     * Store an uploaded image directly at a filesystem directory path, converting to WebP if possible.
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

        $fallbackName = $prefix . '_' . time() . '_' . Str::random(8) . '.' . $this->originalImageExtension($file);
        $file->move($destinationPath, $fallbackName);

        return $fallbackName;
    }

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
