<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    public function buildTree(Collection $categories, ?int $parentId = null): Collection
    {
        return $categories
            ->where('parent_id', $parentId)
            ->map(function (Category $category) use ($categories) {
                $category->setRelation('children', $this->buildTree($categories, $category->id));

                return $category;
            })
            ->values();
    }

    public function options(?Category $exclude = null): Collection
    {
        $query = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($exclude) {
            $query->whereNotIn('id', $this->subtreeIds($exclude));
        }

        $categories = $query->get();

        $flattened = collect();
        $this->flatten($categories, null, 0, $flattened);

        return $flattened;
    }

    public function subtreeIds(Category $category): array
    {
        $ids = [$category->id];
        $children = Category::where('parent_id', $category->id)->pluck('id')->toArray();

        while (! empty($children)) {
            $ids = array_merge($ids, $children);
            $children = Category::whereIn('parent_id', $children)->pluck('id')->toArray();
        }

        return $ids;
    }

    public function wouldCreateCycle(int $categoryId, ?int $parentId): bool
    {
        if ($parentId === null) {
            return false;
        }

        $category = Category::find($categoryId);

        if (! $category) {
            return false;
        }

        return in_array($parentId, $this->subtreeIds($category));
    }

    public function normalizeSlug(?string $slug, string $name): string
    {
        $base = $slug !== null && trim($slug) !== '' ? $slug : $name;

        return Str::slug($base);
    }

    private function flatten(Collection $categories, ?int $parentId, int $depth, Collection &$output): void
    {
        foreach ($categories->where('parent_id', $parentId) as $category) {
            $output->push((object) [
                'id' => $category->id,
                'name' => $category->name,
                'depth' => $depth,
            ]);

            $this->flatten($categories, $category->id, $depth + 1, $output);
        }
    }
}
