<?php

namespace App\Services;

use App\Models\Calculator;
use App\Models\Category;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\ReferenceRange;
use App\Models\Sop;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FacetService
{
    private const TYPE_MAP = [
        'calculator' => Calculator::class,
        'tool' => LaboratoryTool::class,
        'reference-range' => ReferenceRange::class,
        'sop' => Sop::class,
        'interpretation' => InterpretationResource::class,
        'knowledge' => KnowledgeResource::class,
    ];

    private const PIVOT_MAP = [
        Calculator::class => ['table' => 'calculator_category', 'fk' => 'calculator_id', 'tag_table' => 'calculator_tag', 'tag_fk' => 'calculator_id'],
        LaboratoryTool::class => ['table' => 'laboratory_tool_category', 'fk' => 'laboratory_tool_id', 'tag_table' => 'laboratory_tool_tag', 'tag_fk' => 'laboratory_tool_id'],
        ReferenceRange::class => ['table' => 'reference_range_category', 'fk' => 'reference_range_id', 'tag_table' => 'reference_range_tag', 'tag_fk' => 'reference_range_id'],
        Sop::class => ['table' => 'sop_category', 'fk' => 'sop_id', 'tag_table' => 'sop_tag', 'tag_fk' => 'sop_id'],
        InterpretationResource::class => ['table' => 'interpretation_resource_category', 'fk' => 'interpretation_resource_id', 'tag_table' => 'interpretation_resource_tag', 'tag_fk' => 'interpretation_resource_id'],
        KnowledgeResource::class => ['table' => 'knowledge_resource_category', 'fk' => 'knowledge_resource_id', 'tag_table' => 'knowledge_resource_tag', 'tag_fk' => 'knowledge_resource_id'],
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

        $counts = $this->pivotCounts($pivot['table'], $pivot['fk'], $resourceIds, 'category_id');

        return Category::query()
            ->where('is_active', true)
            ->select('categories.*')
            ->selectRaw('COALESCE(cnt.count, 0) AS count')
            ->leftJoinSub($counts, 'cnt', 'cnt.category_id', '=', 'categories.id')
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

        $counts = $this->pivotCounts($pivot['tag_table'], $pivot['tag_fk'], $resourceIds, 'tag_id');

        return Tag::query()
            ->select('tags.*')
            ->selectRaw('COALESCE(cnt.count, 0) AS count')
            ->leftJoinSub($counts, 'cnt', 'cnt.tag_id', '=', 'tags.id')
            ->orderBy('tags.name')
            ->get();
    }

    private function pivotCounts(string $pivotTable, string $resourceColumn, Collection $resourceIds, string $groupColumn): QueryBuilder
    {
        return DB::table($pivotTable)
            ->select($groupColumn)
            ->selectRaw("COUNT(DISTINCT {$resourceColumn}) AS count")
            ->whereIn($resourceColumn, $resourceIds)
            ->whereNotNull($groupColumn)
            ->groupBy($groupColumn);
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

        [$column, $direction] = match ($sort) {
            'az' => ['name', 'asc'],
            'za' => ['name', 'desc'],
            default => ['published_at', 'desc'],
        };

        $query->orderBy($column, $direction)
            ->orderBy('id', $direction);

        $query->with(['categories', 'tags']);

        return $query->paginate($perPage)->withQueryString();
    }

    public function totalCount(Builder $base): int
    {
        return (clone $base)->count();
    }
}
