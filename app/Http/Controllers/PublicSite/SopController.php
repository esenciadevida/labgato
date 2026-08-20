<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Sop;
use App\Services\RecentlyViewedService;
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

        $related = Sop::query()
            ->published()
            ->whereKeyNot($sop->id)
            ->where(function ($q) use ($sop) {
                $q->where('department', $sop->department)
                    ->orWhereHas('categories', fn ($c) => $c->whereIn('categories.id', $sop->categories->pluck('id')));
            })
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        $content = RichTextSanitizer::render($sop->content);

        $pageTitle = $sop->seo_title ?: $sop->name;
        $metaDescription = $sop->seo_description ?: ($sop->short_description ?: "Standard operating procedure: {$sop->name}.");

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
