<?php

namespace App\Services;

use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\ReferenceRange;
use App\Models\Sop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RelatedResourceService
{
    private const TYPES = [
        Calculator::class => ['label' => 'Calculator', 'route' => 'public.calculators.show'],
        Sop::class => ['label' => 'SOP', 'route' => 'public.sops.show'],
        ReferenceRange::class => ['label' => 'Reference Range', 'route' => 'public.reference-ranges.show'],
        KnowledgeResource::class => ['label' => 'Knowledge', 'route' => 'public.knowledge.show'],
        InterpretationResource::class => ['label' => 'Interpretation', 'route' => 'public.interpretation.show'],
        LaboratoryTool::class => ['label' => 'Laboratory Tool', 'route' => 'public.tools.show'],
    ];

    private const PER_TYPE_CANDIDATE_LIMIT = 200;

    private const CACHE_TTL = 3600;

    public function relatedFor(Model $resource, int $limit = 6): Collection
    {
        return Cache::remember(
            'related:'.get_class($resource).':'.$resource->getKey().':'.$limit,
            now()->addSeconds(self::CACHE_TTL),
            fn () => $this->computeRelated($resource, $limit),
        );
    }

    private function computeRelated(Model $resource, int $limit): Collection
    {
        $tagIds = $resource->tags()->pluck('tags.id')->all();
        $categoryIds = $resource->categories()->pluck('categories.id')->all();

        $candidates = collect();

        foreach (self::TYPES as $class => $config) {
            $isSameType = $resource instanceof $class;

            $query = $class::query()
                ->where('status', 'published')
                ->with(['tags:id', 'categories:id'])
                ->latest('published_at')
                ->limit(self::PER_TYPE_CANDIDATE_LIMIT);

            if ($isSameType) {
                $query->whereKeyNot($resource->getKey());
            }

            foreach ($query->get() as $candidate) {
                $sharedTags = $tagIds === []
                    ? 0
                    : $candidate->tags->pluck('id')->intersect($tagIds)->count();

                $sharedCategories = $categoryIds === []
                    ? 0
                    : $candidate->categories->pluck('id')->intersect($categoryIds)->count();

                $score = $sharedTags * 3 + $sharedCategories * 2 + ($isSameType ? 1 : 0);

                $candidates->push([
                    'score' => $score,
                    'published_at' => $candidate->published_at,
                    'resource' => $this->present($candidate, $config),
                ]);
            }
        }

        return $candidates
            ->filter(fn (array $item) => $item['score'] > 0)
            ->sort(function (array $a, array $b): int {
                return [$b['score'], $b['published_at']?->getTimestamp() ?? 0]
                    <=> [$a['score'], $a['published_at']?->getTimestamp() ?? 0];
            })
            ->take($limit)
            ->map(fn (array $item) => $item['resource'])
            ->values();
    }

    private function present(Model $model, array $config): object
    {
        return (object) [
            'title' => $model->name,
            'short_description' => $model->short_description,
            'type_label' => $config['label'],
            'url' => route($config['route'], $model->slug),
            'featured_image_url' => method_exists($model, 'getFeaturedImageUrlAttribute') ? $model->featured_image_url : null,
        ];
    }
}
