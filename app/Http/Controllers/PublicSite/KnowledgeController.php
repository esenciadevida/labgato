<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeResource;
use App\Services\RecentlyViewedService;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q'));
        $categorySlug = trim((string) $request->query('category'));
        $tagSlug = trim((string) $request->query('tag'));
        $difficulty = trim((string) $request->query('difficulty'));

        $knowledge = KnowledgeResource::query()
            ->select(['id', 'slug', 'name', 'short_description', 'topic_category', 'difficulty_level', 'reading_time_minutes'])
            ->with(['categories', 'tags'])
            ->published()
            ->when($query !== '', fn ($q) => $q->search($query))
            ->when($categorySlug !== '', fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('slug', $categorySlug)))
            ->when($tagSlug !== '', fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('slug', $tagSlug)))
            ->when($difficulty !== '', fn ($q) => $q->byDifficulty($difficulty))
            ->orderByDesc('published_at')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        $hasFilters = $query !== '' || $categorySlug !== '' || $tagSlug !== '' || $difficulty !== '';

        $pageTitle = 'Knowledge Hub';
        $metaDescription = 'Educational laboratory knowledge resources covering hematology, clinical chemistry, and laboratory practice.';
        $metaNoindex = $hasFilters;

        return view('public.knowledge.index', compact(
            'knowledge',
            'query',
            'categorySlug',
            'tagSlug',
            'difficulty',
            'hasFilters',
            'pageTitle',
            'metaDescription',
            'metaNoindex',
        ));
    }

    public function show(Request $request, string $slug)
    {
        $knowledge = KnowledgeResource::query()
            ->with(['categories', 'tags', 'contributor'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = KnowledgeResource::query()
            ->published()
            ->whereKeyNot($knowledge->id)
            ->where(function ($q) use ($knowledge) {
                $q->where('topic_category', $knowledge->topic_category)
                    ->orWhere('difficulty_level', $knowledge->difficulty_level);
            })
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        $content = RichTextSanitizer::render($knowledge->content);
        $learningObjectives = $knowledge->learning_objectives
            ? nl2br(e($knowledge->learning_objectives))
            : null;

        $pageTitle = $knowledge->seo_title ?: $knowledge->name;
        $metaDescription = $knowledge->seo_description ?: ($knowledge->short_description ?: "Knowledge resource: {$knowledge->name}.");

        if (auth()->check()) {
            app(RecentlyViewedService::class)->track(auth()->user(), 'knowledge', $knowledge->id);
        }

        return view('public.knowledge.show', compact(
            'knowledge',
            'related',
            'content',
            'learningObjectives',
            'pageTitle',
            'metaDescription',
        ));
    }
}
