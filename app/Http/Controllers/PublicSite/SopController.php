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

        $pageTitle = $sop->effectiveSeoTitle();
        $metaDescription = $sop->effectiveSeoDescription();

        if (auth()->check()) {
            app(RecentlyViewedService::class)->track(auth()->user(), 'sop', $sop->id);
        }

        return view('public.sops.show', compact(
            'sop',
            'related',
            'content',
            'pageTitle',
            'metaDescription',
        ));
    }
}
