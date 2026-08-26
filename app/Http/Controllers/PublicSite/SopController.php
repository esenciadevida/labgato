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
        ) + [
            'ogType' => 'article',
            'ogImage' => $sop->featured_image_url,
            'publishedTime' => optional($sop->published_at)->toIso8601String(),
            'modifiedTime' => optional($sop->updated_at)->toIso8601String(),
            'authorName' => $sop->contributor?->name,
        ]);
    }
}
