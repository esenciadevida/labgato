<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ContentImageService
{
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    private const MAX_BYTES = 2097152;

    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function store(UploadedFile $file): string
    {
        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'featured_image' => 'The featured image must not exceed 2 MB.',
            ]);
        }

        $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->file((string) $file->getRealPath());

        if (! in_array($detectedMime, self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages([
                'featured_image' => 'The featured image must be a JPEG, PNG, or WebP file.',
            ]);
        }

        $extension = self::EXTENSIONS[$detectedMime];
        $filename = Str::random(20).'.'.$extension;
        $path = 'content/'.$filename;

        Storage::disk('public')->putFileAs('content', $file, $filename);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path !== null && $path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
