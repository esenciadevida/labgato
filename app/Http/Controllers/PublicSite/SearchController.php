<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicSite\SearchRequest;
use App\Services\SearchService;

class SearchController extends Controller
{
    public function __invoke(SearchRequest $request, SearchService $searchService)
    {
        $filters = array_filter([
            'type' => $request->input('type', 'all'),
            'category' => $request->input('category'),
            'tag' => $request->input('tag'),
        ], fn ($value) => $value !== null && $value !== '');

        $results = $searchService->search(
            query: (string) $request->input('q'),
            filters: $filters,
            perPage: (int) config('labgoto.search.per_page', 10),
        );

        $pageTitle = $request->filled('q')
            ? 'Search results for "'.$request->input('q').'"'
            : 'Search resources';

        return view('public.search', compact('pageTitle', 'results'));
    }
}
