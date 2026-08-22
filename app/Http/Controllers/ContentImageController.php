<?php

namespace App\Http\Controllers;

use App\Models\ContentImage;
use App\Services\ContentImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentImageController extends Controller
{
    private const TYPE_FOLDERS = [
        'sop' => 'content/sops',
        'knowledge' => 'content/knowledge',
        'interpretation' => 'content/interpretations',
    ];

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp'],
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(self::TYPE_FOLDERS))],
        ]);

        $file = $request->file('image');

        if ($file->getSize() > ContentImageService::MAX_BYTES) {
            return response()->json([
                'message' => 'The image must not exceed '.(ContentImageService::MAX_BYTES / 1048576).' MB.',
                'errors' => ['image' => ['The image must not exceed '.(ContentImageService::MAX_BYTES / 1048576).' MB.']],
            ], 422);
        }

        try {
            $stored = app(ContentImageService::class)->process(
                $file,
                self::TYPE_FOLDERS[$validated['type']],
            );
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        $record = ContentImage::create([
            'uploaded_by' => $request->user()->id,
            'resource_type' => $validated['type'],
            'disk' => 'public',
            'path' => $stored['path'],
            'original_name' => Str::limit((string) $file->getClientOriginalName(), 255, ''),
            'mime_type' => 'image/webp',
            'size_bytes' => $stored['size_bytes'],
            'width' => $stored['width'],
            'height' => $stored['height'],
        ]);

        return response()->json([
            'url' => Storage::disk('public')->url($stored['path']),
            'width' => $stored['width'],
            'height' => $stored['height'],
            'size_bytes' => $record->size_bytes,
        ], 201);
    }
}
