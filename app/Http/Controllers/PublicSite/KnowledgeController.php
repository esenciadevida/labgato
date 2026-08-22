<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeResource;
use App\Services\RecentlyViewedService;
use App\Services\RelatedResourceService;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    public function index()
    {
        return view('public.knowledge.index', [
            'pageTitle' => 'Knowledge Hub',
            'metaDescription' => 'Educational laboratory knowledge resources covering hematology, clinical chemistry, and laboratory practice.',
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $knowledge = KnowledgeResource::query()
            ->with(['categories', 'tags', 'contributor'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = app(RelatedResourceService::class)->relatedFor($knowledge);

        $content = RichTextSanitizer::render($knowledge->content);
        $learningObjectives = $knowledge->learning_objectives
            ? nl2br(e($knowledge->learning_objectives))
            : null;

        $pageTitle = $knowledge->effectiveSeoTitle();
        $metaDescription = $knowledge->effectiveSeoDescription();

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
        ) + [
            'ogType' => 'article',
            'publishedTime' => optional($knowledge->published_at)->toIso8601String(),
            'authorName' => $knowledge->contributor?->name,
        ]);
    }
}
