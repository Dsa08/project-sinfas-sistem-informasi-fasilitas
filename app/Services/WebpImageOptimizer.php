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
     * Store an uploaded image in a public filesystem folder, preferring WebP.
     */
    public function storeAt(UploadedFile $file, string $directory, string $prefix = 'image'): string
    {
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Folder upload tidak dapat dibuat.');
        }

        $name = $prefix . '_' . Str::uuid() . '.webp';
        $destination = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;

        if ($this->convert($file, $destination)) {
            return $name;
        }

        Log::warning('WebP conversion unavailable; storing uploaded image in its original format.', [
            'mime' => $file->getMimeType(),
        ]);

        $extension = $this->originalImageExtension($file);
        $name = $prefix . '_' . Str::uuid() . '.' . $extension;
        $file->move($directory, $name);

        return $name;
    }

    /**
     * Store an uploaded image on a local Laravel disk, preferring WebP.
     */
    public function storeOnDisk(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        Storage::disk($disk)->makeDirectory($directory);

        $relativePath = trim($directory, '/') . '/' . Str::uuid() . '.webp';
        $destination = Storage::disk($disk)->path($relativePath);

        if ($this->convert($file, $destination)) {
            return $relativePath;
        }

        Log::warning('WebP conversion unavailable; storing uploaded image in its original format.', [
            'mime' => $file->getMimeType(),
        ]);

        return $file->store($directory, $disk);
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
