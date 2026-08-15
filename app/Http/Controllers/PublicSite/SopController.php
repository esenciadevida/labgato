<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Sop;
use App\Services\RecentlyViewedService;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;

class SopController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q'));
        $categorySlug = trim((string) $request->query('category'));
        $tagSlug = trim((string) $request->query('tag'));
        $department = trim((string) $request->query('department'));

        $sops = Sop::query()
            ->select(['id', 'slug', 'name', 'short_description', 'department', 'version', 'reviewed_date'])
            ->with(['categories', 'tags'])
            ->published()
            ->when($query !== '', fn ($q) => $q->search($query))
            ->when($categorySlug !== '', fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('slug', $categorySlug)))
            ->when($tagSlug !== '', fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('slug', $tagSlug)))
            ->when($department !== '', fn ($q) => $q->where('department', $department))
            ->orderByDesc('published_at')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        $hasFilters = $query !== '' || $categorySlug !== '' || $tagSlug !== '' || $department !== '';

        $departments = Sop::query()
            ->published()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->selectRaw('DISTINCT department')
            ->orderBy('department')
            ->pluck('department')
            ->all();

        $pageTitle = 'Standard Operating Procedures';
        $metaDescription = 'Standard operating procedures for laboratory professionals, from specimen collection to instrument quality control.';
        $metaNoindex = $hasFilters;

        return view('public.sops.index', compact(
            'sops',
            'query',
            'categorySlug',
            'tagSlug',
            'department',
            'departments',
            'hasFilters',
            'pageTitle',
            'metaDescription',
            'metaNoindex',
        ));
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
