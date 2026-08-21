<?php

namespace App\Services;

use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\RecentlyViewed;
use App\Models\ReferenceRange;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class RecommendationService
{
    public function getRecommendedFor(User $user, int $limit = 6): \Illuminate\Support\Collection
    {
        $recent = RecentlyViewed::where('user_id', $user->id)
            ->latest('viewed_at')
            ->limit(20)
            ->get();

        $excludeIds = $recent->pluck('resource_id')->filter()->all();

        $categoryIds = $this->getCategoryIdsFromRecent($recent);

        $results = collect();

        if (! empty($categoryIds)) {
            $results = $results->merge($this->queryByCategories(Calculator::class, $categoryIds, $excludeIds, $limit));
            $results = $results->merge($this->queryByCategories(LaboratoryTool::class, $categoryIds, $excludeIds, $limit));
            $results = $results->merge($this->queryByCategories(ReferenceRange::class, $categoryIds, $excludeIds, $limit));
            $results = $results->merge($this->queryByCategories(Sop::class, $categoryIds, $excludeIds, $limit));
            $results = $results->merge($this->queryByCategories(InterpretationResource::class, $categoryIds, $excludeIds, $limit));
            $results = $results->merge($this->queryByCategories(KnowledgeResource::class, $categoryIds, $excludeIds, $limit));
        }

        if ($results->isEmpty()) {
            $results = $results->merge($this->queryLatest(Calculator::class, $excludeIds, $limit));
            $results = $results->merge($this->queryLatest(LaboratoryTool::class, $excludeIds, $limit));
            $results = $results->merge($this->queryLatest(ReferenceRange::class, $excludeIds, $limit));
            $results = $results->merge($this->queryLatest(Sop::class, $excludeIds, $limit));
            $results = $results->merge($this->queryLatest(InterpretationResource::class, $excludeIds, $limit));
            $results = $results->merge($this->queryLatest(KnowledgeResource::class, $excludeIds, $limit));
        }

        return $results
            ->unique('id')
            ->sortByDesc('published_at')
            ->take($limit)
            ->values();
    }

    private function getCategoryIdsFromRecent(Collection $recent): array
    {
        $categoryIds = [];

        foreach ($recent as $item) {
            $model = $this->resolveModel($item->resource_type);
            if (! $model) {
                continue;
            }

            $record = $model::query()->find($item->resource_id);
            if ($record && $record->categories()->exists()) {
                $categoryIds = array_merge($categoryIds, $record->categories->pluck('id')->all());
            }
        }

        return array_unique($categoryIds);
    }

    private function queryByCategories(string $modelClass, array $categoryIds, array $excludeIds, int $perType): Collection
    {
        if (empty($categoryIds)) {
            return collect();
        }

        return $modelClass::query()
            ->select(['id', 'slug', 'name', 'short_description', 'published_at'])
            ->published()
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds))
            ->when(! empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->orderByDesc('published_at')
            ->limit($perType)
            ->get()
            ->map(fn ($item) => $this->enrich($item, $modelClass));
    }

    private function queryLatest(string $modelClass, array $excludeIds, int $perType): Collection
    {
        return $modelClass::query()
            ->select(['id', 'slug', 'name', 'short_description', 'published_at'])
            ->published()
            ->when(! empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->orderByDesc('published_at')
            ->limit($perType)
            ->get()
            ->map(fn ($item) => $this->enrich($item, $modelClass));
    }

    private function enrich(object $item, string $modelClass): object
    {
        $item->resource_type = class_basename($modelClass);
        $item->url = $this->getUrl($item, $item->resource_type);

        return $item;
    }

    private function getUrl(object $record, string $type): string
    {
        return match ($type) {
            'Calculator' => route('public.calculators.show', $record->slug),
            'LaboratoryTool' => route('public.tools.show', $record->slug),
            'ReferenceRange' => route('public.reference-ranges.show', $record->slug),
            'Sop' => route('public.sops.show', $record->slug),
            'InterpretationResource' => route('public.interpretation.show', $record->slug),
            'KnowledgeResource' => route('public.knowledge.show', $record->slug),
            default => '#',
        };
    }

    private function resolveModel(string $type): ?string
    {
        return match ($type) {
            'calculator' => Calculator::class,
            'tool' => LaboratoryTool::class,
            'reference-range' => ReferenceRange::class,
            'sop' => Sop::class,
            'interpretation' => InterpretationResource::class,
            'knowledge' => KnowledgeResource::class,
            default => null,
        };
    }
}
