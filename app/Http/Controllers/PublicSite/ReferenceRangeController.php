<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\ReferenceRange;
use App\Services\RecentlyViewedService;
use Illuminate\Http\Request;

class ReferenceRangeController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q'));
        $categorySlug = trim((string) $request->query('category'));

        $referenceRanges = ReferenceRange::query()
            ->select(['id', 'slug', 'name', 'analyte_name', 'short_description', 'sex', 'population_group', 'age_min', 'age_max', 'lower_limit', 'upper_limit', 'unit'])
            ->with(['categories', 'tags'])
            ->published()
            ->when($query !== '', fn ($q) => $q->search($query))
            ->when($categorySlug !== '', fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('slug', $categorySlug)))
            ->orderBy('analyte_name')
            ->orderBy('sex')
            ->orderByDesc('published_at')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        $hasFilters = $query !== '' || $categorySlug !== '';

        $pageTitle = 'Reference Ranges';
        $metaDescription = 'Browse reference ranges for laboratory analytes, including demographic variations, specimen types, and units of measurement.';
        $metaNoindex = $hasFilters;

        return view('public.reference-ranges.index', compact(
            'referenceRanges',
            'query',
            'categorySlug',
            'hasFilters',
            'pageTitle',
            'metaDescription',
            'metaNoindex',
        ));
    }

    public function show(Request $request, string $slug)
    {
        $referenceRange = ReferenceRange::query()
            ->with(['categories', 'tags'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $variations = ReferenceRange::query()
            ->select(['id', 'slug', 'name', 'sex', 'age_min', 'population_group', 'lower_limit', 'upper_limit', 'unit'])
            ->with(['categories'])
            ->published()
            ->forAnalyte($referenceRange->analyte_name)
            ->whereKeyNot($referenceRange->id)
            ->orderBy('sex')
            ->orderBy('age_min')
            ->orderBy('population_group')
            ->limit(50)
            ->get();

        $related = ReferenceRange::query()
            ->select(['id', 'slug', 'name', 'short_description'])
            ->with(['categories', 'tags'])
            ->published()
            ->whereKeyNot($referenceRange->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $referenceRange->categories->pluck('id')))
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        $pageTitle = $referenceRange->seo_title ?: $referenceRange->name;
        $metaDescription = $referenceRange->seo_description ?: ($referenceRange->short_description ?: "Reference range for {$referenceRange->analyte_name}.");

        if (auth()->check()) {
            app(RecentlyViewedService::class)->track(auth()->user(), 'reference-range', $referenceRange->id);
        }

        return view('public.reference-ranges.show', compact(
            'referenceRange',
            'variations',
            'related',
            'pageTitle',
            'metaDescription',
        ));
    }
}
