<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageDownloaderService
{
    /**
     * Download a remote image, validate its type, and store it on the public
     * disk at $destPath. Returns true on success.
     */
    public function downloadAndStore(string $sourceUrl, string $destPath): bool
    {
        try {
            $response = Http::timeout(30)->get($sourceUrl);

            if (! $response->successful()) {
                return false;
            }

            $mime = strtolower(explode(';', (string) $response->header('Content-Type'))[0]);

            if (! in_array($mime, ['image/jpeg', 'image/png'], true)) {
                return false;
            }

            $ext = strtolower(pathinfo((string) parse_url($sourceUrl, PHP_URL_PATH), PATHINFO_EXTENSION));

            if (! in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                $ext = $mime === 'image/png' ? 'png' : 'jpg';
            }

            $ext = $ext === 'jpeg' ? 'jpg' : $ext;
            $destPath = pathinfo($destPath, PATHINFO_EXTENSION) !== ''
                ? $destPath
                : $destPath.'.'.$ext;

            Storage::disk('public')->put($destPath, $response->body());

            return Storage::disk('public')->exists($destPath);
        } catch (\Throwable) {
            return false;
        }
    }
}
