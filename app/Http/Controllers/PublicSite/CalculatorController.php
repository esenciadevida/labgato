<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Calculator;
use App\Services\RecentlyViewedService;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function index(Request $request)
    {
        $categorySlug = trim((string) $request->query('category'));
        $tagSlug = trim((string) $request->query('tag'));
        $query = trim((string) $request->query('q'));

        $calculators = Calculator::query()
            ->select(['id', 'slug', 'name', 'short_description'])
            ->with(['categories', 'tags'])
            ->published()
            ->when($query !== '', fn ($q) => $q->search($query))
            ->when($categorySlug !== '', fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('slug', $categorySlug)))
            ->when($tagSlug !== '', fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('slug', $tagSlug)))
            ->orderByDesc('published_at')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        $hasFilters = $query !== '' || $categorySlug !== '' || $tagSlug !== '';

        $pageTitle = 'Calculators';
        $metaDescription = 'Browse clinical calculators for laboratory professionals, including electrolyte and acid-base calculations.';
        $metaNoindex = $hasFilters;

        return view('public.calculators.index', compact(
            'calculators',
            'categorySlug',
            'tagSlug',
            'query',
            'hasFilters',
            'pageTitle',
            'metaDescription',
            'metaNoindex',
        ));
    }

    public function show(Request $request, string $slug)
    {
        $calculator = Calculator::query()
            ->with(['categories', 'tags'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $pageTitle = $calculator->seo_title ?: $calculator->name;
        $metaDescription = $calculator->seo_description ?: ($calculator->short_description ?: 'Clinical calculator for laboratory professionals.');

        $related = Calculator::query()
            ->published()
            ->whereKeyNot($calculator->id)
            ->where(function ($q) use ($calculator) {
                $q->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $calculator->categories->pluck('id')))
                    ->orWhereHas('tags', fn ($t) => $t->whereIn('tags.id', $calculator->tags->pluck('id')));
            })
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        if (auth()->check()) {
            app(RecentlyViewedService::class)->track(auth()->user(), 'calculator', $calculator->id);
        }

        return view('public.calculators.show', compact('calculator', 'related', 'pageTitle', 'metaDescription'));
    }
}
