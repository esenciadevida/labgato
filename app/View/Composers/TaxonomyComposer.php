<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\View\View;

class TaxonomyComposer
{
    public function compose(View $view): void
    {
        try {
            $view->with('categories', Category::query()->where('is_active', true)->orderBy('name')->get());
            $view->with('tags', Tag::query()->orderBy('name')->get());
        } catch (\Throwable $e) {
            $view->with('categories', collect());
            $view->with('tags', collect());
        }
    }
}
