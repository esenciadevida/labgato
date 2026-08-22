<?php

namespace App\Http\Controllers;

use App\Support\RichTextSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentPreviewController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:400000'],
        ]);

        return response()->json([
            'html' => RichTextSanitizer::render($validated['content']),
        ]);
    }
}
