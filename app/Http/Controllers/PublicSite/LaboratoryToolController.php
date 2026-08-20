<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\LaboratoryTool;
use App\Services\RecentlyViewedService;
use Illuminate\Http\Request;

class LaboratoryToolController extends Controller
{
    public function index()
    {
        return view('public.tools.index', [
            'pageTitle' => 'Laboratory Tools',
            'metaDescription' => 'Practical laboratory tools for daily operations, including dilution calculations and centrifugation conversions.',
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $tool = LaboratoryTool::query()
            ->with(['categories', 'tags'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $pageTitle = $tool->seo_title ?: $tool->name;
        $metaDescription = $tool->seo_description ?: ($tool->short_description ?: 'Laboratory tool for professionals.');

        $related = LaboratoryTool::query()
            ->published()
            ->whereKeyNot($tool->id)
            ->where(function ($q) use ($tool) {
                $q->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $tool->categories->pluck('id')))
                    ->orWhereHas('tags', fn ($t) => $t->whereIn('tags.id', $tool->tags->pluck('id')));
            })
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        if (auth()->check()) {
            app(RecentlyViewedService::class)->track(auth()->user(), 'tool', $tool->id);
        }

        return view('public.tools.show', compact('tool', 'related', 'pageTitle', 'metaDescription'));
    }
}
