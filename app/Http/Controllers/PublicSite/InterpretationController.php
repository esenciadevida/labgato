<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\InterpretationResource;
use App\Services\RecentlyViewedService;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;

class InterpretationController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q'));
        $categorySlug = trim((string) $request->query('category'));
        $tagSlug = trim((string) $request->query('tag'));

        $interpretations = InterpretationResource::query()
            ->select(['id', 'slug', 'name', 'short_description', 'reading_time_minutes', 'related_analytes'])
            ->with(['categories', 'tags'])
            ->published()
            ->when($query !== '', fn ($q) => $q->search($query))
            ->when($categorySlug !== '', fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('slug', $categorySlug)))
            ->when($tagSlug !== '', fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('slug', $tagSlug)))
            ->orderByDesc('published_at')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        $hasFilters = $query !== '' || $categorySlug !== '' || $tagSlug !== '';

        $pageTitle = 'Interpretations';
        $metaDescription = 'Browse laboratory result interpretation guides covering common analyte patterns and clinical scenarios.';
        $metaNoindex = $hasFilters;

        return view('public.interpretation.index', compact(
            'interpretations',
            'query',
            'categorySlug',
            'tagSlug',
            'hasFilters',
            'pageTitle',
            'metaDescription',
            'metaNoindex',
        ));
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
