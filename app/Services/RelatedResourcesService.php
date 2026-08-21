<?php

namespace App\Services;

use App\Models\Resource;
use Illuminate\Support\Collection;

class RelatedResourcesService
{
    public function getRelated(Resource $resource, string $type, int $limit = 4): Collection
    {
        $categoryIds = $resource->categories->pluck('id');
        $tagIds = $resource->tags->pluck('id');

        $query = Resource::where('status', 'published')
            ->where(function ($q) use ($categoryIds, $tagIds) {
                $q->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('category_id', $categoryIds);
                })
                    ->orWhereHas('tags', function ($q) use ($tagIds) {
                        $q->whereIn('tag_id', $tagIds);
                    });
            });

        $sameType = $query->where('type', $type)->latest('updated_at')->take($limit)->get();
        $otherTypes = $query->where('type', '!=', $type)->latest('updated_at')->take($limit)->get();

        $related = $sameType->concat($otherTypes)->unique('id')->values()->take($limit);

        return $related->map(function ($item) {
            return [
                'title' => $item->name,
                'type' => $item->type,
                'url' => route("public.{$item->type}.show", $item->slug),
                'short_description' => $item->short_description,
            ];
        });
    }
}
