<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Calculator;
use App\Models\ReferenceRange;
use App\Services\RecentlyViewedService;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function index()
    {
        return view('public.calculators.index', [
            'pageTitle' => 'Calculators',
            'metaDescription' => 'Browse clinical calculators for laboratory professionals, including electrolyte and acid-base calculations.',
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $calculator = Calculator::query()
            ->with(['categories', 'tags'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        if (auth()->check()) {
            app(RecentlyViewedService::class)->track(auth()->user(), 'calculator', $calculator->id);
        }

        return $this->renderShow($calculator);
    }

    public function renderShow(Calculator $calculator)
    {
        $pageTitle = $calculator->seo_title ?: $calculator->name;
        $metaDescription = $calculator->seo_description ?: ($calculator->short_description ?: 'Clinical calculator for laboratory professionals.');

        $parameterKeys = collect($calculator->output_schema)
            ->pluck('reference_key')
            ->filter(fn ($key) => is_string($key) && $key !== '')
            ->unique()
            ->values();

        $referenceRanges = $parameterKeys->isNotEmpty()
            ? ReferenceRange::query()
                ->published()
                ->whereIn('parameter_key', $parameterKeys)
                ->get()
            : collect();

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

        return view('public.calculators.show', compact(
            'calculator',
            'related',
            'referenceRanges',
            'pageTitle',
            'metaDescription',
        ));
    }
}
