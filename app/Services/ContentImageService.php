<?php

namespace App\Services;

use App\Models\ContentImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ContentImageService
{
    public const MAX_BYTES = 5 * 1024 * 1024;

    private const MAX_WIDTH = 1600;

    private const WEBP_QUALITY = 82;

    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function store(UploadedFile $file): string
    {
        return $this->process($file, 'content')['path'];
    }

    /**
     * Validate, downscale and re-encode an uploaded image as WebP.
     *
     * @return array{path: string, size_bytes: int, width: int|null, height: int|null}
     */
    public function process(UploadedFile $file, string $folder = 'content'): array
    {
        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'image' => 'The image must not exceed '.(self::MAX_BYTES / 1048576).' MB.',
            ]);
        }

        $realPath = (string) $file->getRealPath();
        $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->file($realPath);

        if (! in_array($detectedMime, self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages([
                'image' => 'The image must be a JPEG, PNG, or WebP file.',
            ]);
        }

        $binary = (string) file_get_contents($realPath);
        $source = @imagecreatefromstring($binary);

        if ($source === false) {
            return $this->storeOriginal($binary, $detectedMime, $folder);
        }

        $image = $this->orientFromExif($source, $detectedMime, $realPath);

        if (imagesx($image) > self::MAX_WIDTH) {
            $scaled = $this->scaleDown($image);
            imagedestroy($image);
            $image = $scaled;
        }

        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, self::WEBP_QUALITY);
        $encoded = (string) ob_get_clean();

        [$width, $height] = [imagesx($image), imagesy($image)];
        imagedestroy($image);

        if ($encoded === '') {
            throw ValidationException::withMessages([
                'image' => 'The image could not be processed. Please try a different file.',
            ]);
        }

        $directory = trim($folder, '/').'/'.now()->format('Y/m');
        $path = $directory.'/'.Str::random(32).'.webp';

        Storage::disk('public')->put($path, $encoded);

        return [
            'path' => $path,
            'size_bytes' => strlen($encoded),
            'width' => $width,
            'height' => $height,
        ];
    }

    public function delete(?string $path): void
    {
        if ($path !== null && $path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Mark uploaded images referenced inside saved content as attached to a resource,
     * so they are excluded from orphan cleanup.
     */
    public function syncAttachments(string|array $texts, string $resourceType, int $resourceId): int
    {
        $haystack = is_array($texts) ? implode("\n", $texts) : $texts;

        preg_match_all('/\bcontent\/[A-Za-z0-9_-]+(?:\/[A-Za-z0-9_-]+)*\.(?:webp|jpe?g|png)\b/i', $haystack, $matches);

        if (empty($matches[0])) {
            return 0;
        }

        $images = ContentImage::query()
            ->whereNull('attached_at')
            ->whereIn('path', array_values(array_unique($matches[0])))
            ->get();

        foreach ($images as $image) {
            $image->attachTo($resourceType, $resourceId);
        }

        return $images->count();
    }

    /**
     * Fallback when GD cannot decode the file: keep original bytes untouched.
     *
     * @return array{path: string, size_bytes: int, width: int|null, height: int|null}
     */
    private function storeOriginal(string $binary, string $mime, string $folder): array
    {
        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            default => 'webp',
        };

        $directory = trim($folder, '/').'/'.now()->format('Y/m');
        $path = $directory.'/'.Str::random(32).'.'.$extension;

        Storage::disk('public')->put($path, $binary);

        $dimensions = getimagesizefromstring($binary);

        return [
            'path' => $path,
            'size_bytes' => strlen($binary),
            'width' => $dimensions === false ? null : $dimensions[0],
            'height' => $dimensions === false ? null : $dimensions[1],
        ];
    }

    private function orientFromExif(\GdImage $image, string $mime, string $realPath): \GdImage
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = (int) (@exif_read_data($realPath)['Orientation'] ?? 1);

        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => null,
        };

        if ($angle === null) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    private function scaleDown(\GdImage $source): \GdImage
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $targetHeight = max(1, (int) round($sourceHeight * (self::MAX_WIDTH / $sourceWidth)));

        $target = imagecreatetruecolor(self::MAX_WIDTH, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            self::MAX_WIDTH,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );

        return $target;
    }
}
