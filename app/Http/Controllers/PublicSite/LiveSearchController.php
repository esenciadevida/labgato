<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use App\Support\SearchResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveSearchController extends Controller
{
    public function __invoke(Request $request, SearchService $searchService): JsonResponse
    {
        $query = trim(strip_tags((string) $request->query('q', '')));
        $type = trim(strip_tags((string) $request->query('type', '')));
        $category = trim(strip_tags((string) $request->query('category', '')));
        $tag = trim(strip_tags((string) $request->query('tag', '')));
        $limit = max(1, min((int) $request->query('limit', 6), 20));

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => [], 'total' => 0]);
        }

        $filters = array_filter([
            'type' => $type,
            'category' => $category,
            'tag' => $tag,
        ], fn ($value) => $value !== '' && $value !== 'all');

        $paginator = $searchService->search($query, $filters, $limit);

        $results = $paginator->getCollection()
            ->map(fn (SearchResult $item) => $item->toArray())
            ->values()
            ->all();

        return response()->json([
            'results' => $results,
            'total' => $paginator->total(),
        ]);
    }
}
