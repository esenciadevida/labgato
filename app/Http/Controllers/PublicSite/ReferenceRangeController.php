<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\ReferenceRange;
use App\Services\RecentlyViewedService;
use Illuminate\Http\Request;

class ReferenceRangeController extends Controller
{
    public function index()
    {
        return view('public.reference-ranges.index', [
            'pageTitle' => 'Reference Ranges',
            'metaDescription' => 'Browse reference ranges for laboratory analytes, including demographic variations, specimen types, and units of measurement.',
        ]);
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

        $sops = $referenceRange->sops()
            ->published()
            ->orderByDesc('published_at')
            ->limit(5)
            ->get(['sops.id', 'sops.slug', 'sops.name']);

        $pageTitle = $referenceRange->seo_title ?: $referenceRange->name;
        $metaDescription = $referenceRange->seo_description ?: ($referenceRange->short_description ?: "Reference range for {$referenceRange->analyte_name}.");

        if (auth()->check()) {
            app(RecentlyViewedService::class)->track(auth()->user(), 'reference-range', $referenceRange->id);
        }

        return view('public.reference-ranges.show', compact(
            'referenceRange',
            'variations',
            'related',
            'sops',
            'pageTitle',
            'metaDescription',
        ));
    }
}
