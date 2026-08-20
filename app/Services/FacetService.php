<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FacetService
{
    private const TYPE_MAP = [
        'calculator' => \App\Models\Calculator::class,
        'tool' => \App\Models\LaboratoryTool::class,
        'reference-range' => \App\Models\ReferenceRange::class,
        'sop' => \App\Models\Sop::class,
        'interpretation' => \App\Models\InterpretationResource::class,
        'knowledge' => \App\Models\KnowledgeResource::class,
    ];

    private const PIVOT_MAP = [
        \App\Models\Calculator::class => ['table' => 'calculator_category', 'fk' => 'calculator_id', 'tag_table' => 'calculator_tag', 'tag_fk' => 'calculator_id'],
        \App\Models\LaboratoryTool::class => ['table' => 'laboratory_tool_category', 'fk' => 'laboratory_tool_id', 'tag_table' => 'laboratory_tool_tag', 'tag_fk' => 'laboratory_tool_id'],
        \App\Models\ReferenceRange::class => ['table' => 'reference_range_category', 'fk' => 'reference_range_id', 'tag_table' => 'reference_range_tag', 'tag_fk' => 'reference_range_id'],
        \App\Models\Sop::class => ['table' => 'sop_category', 'fk' => 'sop_id', 'tag_table' => 'sop_tag', 'tag_fk' => 'sop_id'],
        \App\Models\InterpretationResource::class => ['table' => 'interpretation_resource_category', 'fk' => 'interpretation_resource_id', 'tag_table' => 'interpretation_resource_tag', 'tag_fk' => 'interpretation_resource_id'],
        \App\Models\KnowledgeResource::class => ['table' => 'knowledge_resource_category', 'fk' => 'knowledge_resource_id', 'tag_table' => 'knowledge_resource_tag', 'tag_fk' => 'knowledge_resource_id'],
    ];

    public function modelClass(string $type): string
    {
        return self::TYPE_MAP[$type] ?? throw new \InvalidArgumentException("Unknown resource type: {$type}");
    }

    public function baseQuery(string $modelClass, string $search = ''): Builder
    {
        $query = $modelClass::query()->where('status', 'published');

        if ($search !== '') {
            $query->search($search);
        }

        return $query;
    }

    public function categoriesWithCounts(Builder $base, string $modelClass, array $selectedTagIds = []): Collection
    {
        $query = clone $base;
        $pivot = self::PIVOT_MAP[$modelClass];

        if (! empty($selectedTagIds)) {
            $tagIds = $selectedTagIds;
            $query->whereHas('tags', fn (Builder $q) => $q->whereIn('tags.id', $tagIds));
        }

        $resourceIds = $query->pluck((new $modelClass)->getKeyName());

        return Category::query()
            ->where('is_active', true)
            ->select('categories.*')
            ->selectRaw("COALESCE((SELECT COUNT(DISTINCT pc.{$pivot['fk']}) FROM {$pivot['table']} pc WHERE pc.category_id = categories.id AND pc.{$pivot['fk']} IN (" . $resourceIds->implode(',') . ")), 0) as count")
            ->orderBy('categories.sort_order')
            ->orderBy('categories.name')
            ->get();
    }

    public function tagsWithCounts(Builder $base, string $modelClass, array $selectedCategoryIds = []): Collection
    {
        $query = clone $base;
        $pivot = self::PIVOT_MAP[$modelClass];

        if (! empty($selectedCategoryIds)) {
            $catIds = $selectedCategoryIds;
            $query->whereHas('categories', fn (Builder $q) => $q->whereIn('categories.id', $catIds));
        }

        $resourceIds = $query->pluck((new $modelClass)->getKeyName());

        if ($resourceIds->isEmpty()) {
            return collect();
        }

        $idList = $resourceIds->implode(',');

        return Tag::query()
            ->select('tags.*')
            ->selectRaw("COALESCE((SELECT COUNT(DISTINCT pt.{$pivot['tag_fk']}) FROM {$pivot['tag_table']} pt WHERE pt.tag_id = tags.id AND pt.{$pivot['tag_fk']} IN ({$idList})), 0) as count")
            ->orderBy('tags.name')
            ->get();
    }

    public function results(
        Builder $base,
        array $categoryIds = [],
        array $tagIds = [],
        string $sort = 'newest',
        int $perPage = 24,
    ): LengthAwarePaginator {
        $query = clone $base;

        if (! empty($categoryIds)) {
            $catIds = $categoryIds;
            $query->whereHas('categories', fn (Builder $q) => $q->whereIn('categories.id', $catIds));
        }

        if (! empty($tagIds)) {
            $tIds = $tagIds;
            $query->whereHas('tags', fn (Builder $q) => $q->whereIn('tags.id', $tIds));
        }

        $query->orderBy(match ($sort) {
            'az' => 'name',
            'za' => 'name',
            default => 'published_at',
        }, match ($sort) {
            'za' => 'desc',
            'az' => 'asc',
            default => 'desc',
        });

        $query->with(['categories', 'tags']);

        return $query->paginate($perPage)->withQueryString();
    }

    public function totalCount(Builder $base): int
    {
        return (clone $base)->count();
    }
}
