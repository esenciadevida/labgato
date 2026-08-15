<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\LaboratoryTool;
use App\Services\RecentlyViewedService;
use Illuminate\Http\Request;

class LaboratoryToolController extends Controller
{
    public function index(Request $request)
    {
        $categorySlug = trim((string) $request->query('category'));
        $tagSlug = trim((string) $request->query('tag'));
        $query = trim((string) $request->query('q'));

        $tools = LaboratoryTool::query()
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

        $pageTitle = 'Laboratory Tools';
        $metaDescription = 'Practical laboratory tools for daily operations, including dilution calculations and centrifugation conversions.';
        $metaNoindex = $hasFilters;

        return view('public.tools.index', compact(
            'tools',
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
