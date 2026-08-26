<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\InterpretationResource;
use App\Services\RecentlyViewedService;
use App\Services\RelatedResourceService;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;

class InterpretationController extends Controller
{
    public function index()
    {
        return view('public.interpretation.index', [
            'pageTitle' => 'Interpretations',
            'metaDescription' => 'Browse laboratory result interpretation guides covering common analyte patterns and clinical scenarios.',
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $interpretation = InterpretationResource::query()
            ->with(['categories', 'tags', 'contributor'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = app(RelatedResourceService::class)->relatedFor($interpretation);

        $content = RichTextSanitizer::render($interpretation->content);
        [$heroImage, $heroImageSrc] = RichTextSanitizer::extractFirstImage($content);
        $content = $heroImage ? RichTextSanitizer::stripFirstImage($content) : $content;

        $readingTime = $interpretation->reading_time_minutes
            ?? max(1, (int) ceil(str_word_count(strip_tags($interpretation->content)) / 200));

        $pageTitle = $interpretation->effectiveSeoTitle();
        $metaDescription = $interpretation->effectiveSeoDescription();

        if (auth()->check()) {
            app(RecentlyViewedService::class)->track(auth()->user(), 'interpretation', $interpretation->id);
        }

        return view('public.interpretation.show', compact(
            'interpretation',
            'related',
            'content',
            'pageTitle',
            'metaDescription',
            'heroImage',
            'heroImageSrc',
            'readingTime',
        ) + [
            'ogType' => 'article',
            'ogImage' => $interpretation->featured_image_url ?? $heroImageSrc,
            'publishedTime' => optional($interpretation->published_at)->toIso8601String(),
            'modifiedTime' => optional($interpretation->updated_at)->toIso8601String(),
            'authorName' => $interpretation->contributor?->name,
        ]);
    }
}
