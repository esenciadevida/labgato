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

    private const SQUARE_SIZE = 512;

    /** Responsive width variants written next to each content image. */
    final public const VARIANTS = [320, 640, 960, 1280, 1600];

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
     * Validate, downscale, optionally square-crop, and re-encode an uploaded
     * image as WebP, plus responsive width variants for content images.
     *
     * @return array{path: string, size_bytes: int, width: int|null, height: int|null, variants: array<int, int>}
     */
    public function process(UploadedFile $file, string $folder = 'content', bool $square = false): array
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
            $result = $this->storeOriginal($binary, $detectedMime, $folder);
            $result['variants'] = [];

            return $result;
        }

        $image = $this->orientFromExif($source, $detectedMime, $realPath);

        if ($square) {
            $image = $this->centerCropSquare($image);
        } elseif (imagesx($image) > self::MAX_WIDTH) {
            $scaled = $this->scaleDown($image);
            imagedestroy($image);
            $image = $scaled;
        }

        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, self::WEBP_QUALITY);
        $encoded = (string) ob_get_clean();

        [$width, $height] = [imagesx($image), imagesy($image)];

        if ($encoded === '') {
            imagedestroy($image);

            throw ValidationException::withMessages([
                'image' => 'The image could not be processed. Please try a different file.',
            ]);
        }

        $directory = trim($folder, '/').'/'.now()->format('Y/m');
        $name = Str::random(32);
        $path = $directory.'/'.$name.'.webp';

        Storage::disk('public')->put($path, $encoded);

        $variants = $square ? [] : $this->writeVariants($image, $directory, $name, $width);

        imagedestroy($image);

        return [
            'path' => $path,
            'size_bytes' => strlen($encoded),
            'width' => $width,
            'height' => $height,
            'variants' => $variants,
        ];
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $this->deleteWithVariants($path);
    }

    /**
     * Delete a base image together with any responsive variants that share
     * its file name.
     */
    public function deleteWithVariants(string $path): void
    {
        $disk = Storage::disk('public');

        $disk->delete($path);

        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'webp') {
            return;
        }

        $stem = pathinfo($path, PATHINFO_DIRNAME).'/'.pathinfo($path, PATHINFO_FILENAME);

        foreach (self::VARIANTS as $width) {
            $disk->delete("{$stem}-{$width}w.webp");
        }
    }

    /**
     * Generate the responsive width variants for an already-stored WebP image
     * (used to backfill images uploaded before variants existed).
     *
     * @return array<int, int> widths written
     */
    public function generateVariantsForPath(string $path, bool $force = false): array
    {
        $disk = Storage::disk('public');

        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'webp' || ! $disk->exists($path)) {
            return [];
        }

        $binary = $disk->get($path);
        if ($binary === null) {
            return [];
        }

        $image = @imagecreatefromstring($binary);
        if ($image === false) {
            return [];
        }

        $width = imagesx($image);
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $name = pathinfo($path, PATHINFO_FILENAME);

        if (! $force) {
            foreach (self::VARIANTS as $variantWidth) {
                if ($variantWidth < $width && $disk->exists("{$directory}/{$name}-{$variantWidth}w.webp")) {
                    imagedestroy($image);

                    return [];
                }
            }
        }

        $written = $this->writeVariants($image, $directory, $name, $width);

        imagedestroy($image);

        return $written;
    }

    /**
     * Encode responsive WebP variants of a decoded image at the configured
     * widths, skipping any that would upscale.
     *
     * @return array<int, int> widths written
     */
    private function writeVariants(\GdImage $image, string $directory, string $name, int $sourceWidth): array
    {
        $disk = Storage::disk('public');
        $written = [];

        foreach (self::VARIANTS as $width) {
            if ($width >= $sourceWidth) {
                continue;
            }

            $variant = $this->resize($image, $width);

            ob_start();
            imagewebp($variant, null, self::WEBP_QUALITY);
            $encoded = (string) ob_get_clean();
            imagedestroy($variant);

            if ($encoded === '') {
                continue;
            }

            $disk->put("{$directory}/{$name}-{$width}w.webp", $encoded);
            $written[] = $width;
        }

        return $written;
    }

    private function resize(\GdImage $source, int $targetWidth): \GdImage
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $targetHeight = max(1, (int) round($sourceHeight * ($targetWidth / $sourceWidth)));

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
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

        return $target;
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
        return $this->resize($source, self::MAX_WIDTH);
    }

    /**
     * Center-crop an image to a square, resizing down to SQUARE_SIZE if the
     * source is larger. Never upscales, so small images are preserved.
     */
    private function centerCropSquare(\GdImage $source): \GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $side = (int) min($width, $height);
        $target = (int) min($side, self::SQUARE_SIZE);

        if ($target === $width && $width === $height) {
            return $source;
        }

        $srcX = (int) max(0, floor(($width - $side) / 2));
        $srcY = (int) max(0, floor(($height - $side) / 2));

        $cropped = imagecreatetruecolor($target, $target);
        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);
        imagecopyresampled(
            $cropped,
            $source,
            0,
            0,
            $srcX,
            $srcY,
            $target,
            $target,
            $side,
            $side,
        );

        imagedestroy($source);

        return $cropped;
    }
}
