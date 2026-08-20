<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\InterpretationResource;
use App\Services\RecentlyViewedService;
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

        $related = InterpretationResource::query()
            ->published()
            ->whereKeyNot($interpretation->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $interpretation->categories->pluck('id')))
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        $content = RichTextSanitizer::render($interpretation->content);

        $pageTitle = $interpretation->seo_title ?: $interpretation->name;
        $metaDescription = $interpretation->seo_description ?: ($interpretation->short_description ?: "Interpretation guide: {$interpretation->name}.");

        if (auth()->check()) {
            app(RecentlyViewedService::class)->track(auth()->user(), 'interpretation', $interpretation->id);
        }

        return view('public.interpretation.show', compact(
            'interpretation',
            'related',
            'content',
            'pageTitle',
            'metaDescription',
        ));
    }
}
