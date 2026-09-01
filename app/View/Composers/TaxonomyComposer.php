<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TaxonomyComposer
{
    public const CATEGORIES_KEY = 'taxonomy:categories';
    public const TAGS_KEY = 'taxonomy:tags';

    private const TTL = 3600;

    public function compose(View $view): void
    {
        try {
            $view->with('categories', $this->categories());
            $view->with('tags', $this->tags());
        } catch (\Throwable $e) {
            $view->with('categories', collect());
            $view->with('tags', collect());
        }
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CATEGORIES_KEY);
        Cache::forget(self::TAGS_KEY);
    }

    private function categories(): \Illuminate\Support\Collection
    {
        return Cache::remember(self::CATEGORIES_KEY, self::TTL, function () {
            return Category::query()->where('is_active', true)->orderBy('name')->get();
        });
    }

    private function tags(): \Illuminate\Support\Collection
    {
        return Cache::remember(self::TAGS_KEY, self::TTL, function () {
            return Tag::query()->orderBy('name')->get();
        });
    }
}
