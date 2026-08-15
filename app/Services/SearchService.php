<?php

namespace App\Services;

use App\Models\SearchQuery;
use App\Support\SearchResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SearchService
{
    /**
     * @var array<string, array<callable>>
     */
    protected array $providers = [];

    public function registerProvider(string $type, callable $provider): void
    {
        $this->providers[$type][] = $provider;
    }

    public function search(string $query, array $filters = [], int $perPage = 10, ?int $page = null): LengthAwarePaginator
    {
        $query = trim(strip_tags($query));
        $page = $page ?? Paginator::resolveCurrentPage();

        if ($query === '') {
            return new Paginator([], 0, $perPage, $page, $this->paginatorOptions());
        }

        $results = collect();

        foreach ($this->providers as $type => $providers) {
            if (isset($filters['type']) && $filters['type'] !== 'all' && $filters['type'] !== $type) {
                continue;
            }

            foreach ($providers as $provider) {
                foreach ($provider($query, $filters) as $result) {
                    $results->push($result);
                }
            }
        }

        $results = $results
            ->values()
            ->map(fn (SearchResult $result, int $index) => ['index' => $index, 'result' => $result])
            ->sort(function (array $a, array $b) {
                $aTimestamp = $a['result']->publishedAt?->getTimestamp() ?? 0;
                $bTimestamp = $b['result']->publishedAt?->getTimestamp() ?? 0;

                return $bTimestamp <=> $aTimestamp ?: $a['index'] <=> $b['index'];
            })
            ->map(fn (array $item) => $item['result'])
            ->values();

        $total = $results->count();
        $items = $results->slice(($page - 1) * $perPage, $perPage)->values();

        $this->logQuery($query, $total);

        return new Paginator($items, $total, $perPage, $page, $this->paginatorOptions());
    }

    public function logQuery(string $query, int $resultsCount): void
    {
        if (! config('labgoto.search.log_queries')) {
            return;
        }

        try {
            $data = [
                'query' => Str::limit($query, (int) config('labgoto.search.max_query_length', 255), ''),
                'results_count' => $resultsCount,
                'user_id' => Auth::id(),
            ];

            if (config('labgoto.search.log_ip')) {
                $data['ip_address'] = request()->ip();
            }

            $userAgent = request()->userAgent();
            if ($userAgent) {
                $data['user_agent'] = Str::limit($userAgent, 500, '');
            }

            SearchQuery::create($data);
        } catch (\Throwable $e) {
            Log::warning('Failed to log search query: '.$e->getMessage());
        }
    }

    private function paginatorOptions(): array
    {
        return [
            'path' => Paginator::resolveCurrentPath(),
            'query' => request()->query(),
        ];
    }
}
