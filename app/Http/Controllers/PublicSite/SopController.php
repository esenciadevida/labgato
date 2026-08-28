<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Sop;
use App\Services\RecentlyViewedService;
use App\Services\RelatedResourceService;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;

class SopController extends Controller
{
    public function index()
    {
        return view('public.sops.index', [
            'pageTitle' => 'Standard Operating Procedures',
            'metaDescription' => 'Standard operating procedures for laboratory professionals, from specimen collection to instrument quality control.',
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $sop = Sop::query()
            ->with(['categories', 'tags', 'contributor'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = app(RelatedResourceService::class)->relatedFor($sop);

        $content = RichTextSanitizer::render($sop->content);
        [$heroImage, $heroImageSrc, $heroImageAlt] = RichTextSanitizer::extractFirstImage($content);
        $content = $heroImage ? RichTextSanitizer::stripFirstImage($content) : $content;

        $readingTime = max(1, (int) ceil(str_word_count(strip_tags($sop->content)) / 200));

        $previous = $sop->published_at
            ? Sop::query()->published()
                ->where('published_at', '<', $sop->published_at)
                ->orderByDesc('published_at')
                ->first(['id', 'slug', 'name'])
            : null;

        $next = $sop->published_at
            ? Sop::query()->published()
                ->where('published_at', '>', $sop->published_at)
                ->orderBy('published_at')
                ->first(['id', 'slug', 'name'])
            : null;

        $revisions = $sop->versions()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['version_number', 'change_summary', 'created_at']);

        $pageTitle = $sop->effectiveSeoTitle();
        $metaDescription = $sop->effectiveSeoDescription();

        if (auth()->check()) {
            app(RecentlyViewedService::class)->track(auth()->user(), 'sop', $sop->id);
        }

        return view('public.sops.show', compact(
            'sop',
            'related',
            'content',
            'revisions',
            'pageTitle',
            'metaDescription',
            'heroImage',
            'heroImageSrc',
            'heroImageAlt',
            'previous',
            'next',
            'readingTime',
        ) + [
            'ogType' => 'article',
            'ogImage' => $sop->featured_image_url ?? $heroImageSrc,
            'publishedTime' => optional($sop->published_at)->toIso8601String(),
            'modifiedTime' => optional($sop->updated_at)->toIso8601String(),
            'authorName' => $sop->contributor?->name,
        ]);
    }
}
