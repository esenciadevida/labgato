<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Calculator;
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
