<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UploadedImageOptimizer
{
    private const MAX_WIDTH = 1600;

    private const MAX_HEIGHT = 1600;

    private const WEBP_QUALITY = 78;

    public function store(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        string $errorField = 'image',
    ): string {
        $sourcePath = $file->getRealPath();

        if ($sourcePath === false || ! function_exists('imagewebp')) {
            $this->invalidImage($errorField);
        }

        $information = @getimagesize($sourcePath);

        if ($information === false) {
            $this->invalidImage($errorField);
        }

        $source = $this->decode($sourcePath, $information[2], $errorField);
        $source = $this->orientJpeg($source, $sourcePath, $information[2]);

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, self::MAX_WIDTH / $sourceWidth, self::MAX_HEIGHT / $sourceHeight);
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($target === false) {
            $this->invalidImage($errorField);
        }

        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $transparent);
        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );

        $temporaryPath = tempnam(sys_get_temp_dir(), 'lifers-image-');

        if ($temporaryPath === false) {
            $this->invalidImage($errorField);
        }

        try {
            if (! imagewebp($target, $temporaryPath, self::WEBP_QUALITY)) {
                $this->invalidImage($errorField);
            }

            $contents = file_get_contents($temporaryPath);
            $path = trim($directory, '/').'/'.Str::uuid().'.webp';

            if ($contents === false || ! Storage::disk($disk)->put($path, $contents)) {
                throw ValidationException::withMessages([
                    $errorField => 'L’image optimisée n’a pas pu être enregistrée.',
                ]);
            }

            return $path;
        } finally {
            @unlink($temporaryPath);
        }
    }

    private function decode(string $path, int $type, string $errorField): \GdImage
    {
        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $image instanceof \GdImage) {
            $this->invalidImage($errorField);
        }

        return $image;
    }

    private function orientJpeg(\GdImage $image, string $path, int $type): \GdImage
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = @exif_read_data($path, 'IFD0')['Orientation'] ?? 1;

        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, in_array($orientation, [2, 5], true) ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
        }

        $angle = match ($orientation) {
            3, 4 => 180,
            5, 6 => -90,
            7, 8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        return $rotated instanceof \GdImage ? $rotated : $image;
    }

    private function invalidImage(string $errorField): never
    {
        throw ValidationException::withMessages([
            $errorField => 'Cette image n’a pas pu être optimisée. Choisis une image JPEG, PNG ou WebP valide.',
        ]);
    }
}
