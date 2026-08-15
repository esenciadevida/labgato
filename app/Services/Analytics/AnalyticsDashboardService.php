<?php

namespace App\Services\Analytics;

use App\Enums\ResourceStatus;
use App\Models\AiUsage;
use App\Models\AnalyticsDailyMetric;
use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\ReferenceRange;
use App\Models\SearchQuery;
use App\Models\Sop;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsDashboardService
{
    private const RESOURCE_MODELS = [
        Calculator::class,
        LaboratoryTool::class,
        ReferenceRange::class,
        Sop::class,
        InterpretationResource::class,
        KnowledgeResource::class,
    ];

    public function getKpiMetrics(): array
    {
        return Cache::remember('analytics:today:kpis', 900, function () {
            $publishedCount = 0;
            $underReviewCount = 0;

            foreach (self::RESOURCE_MODELS as $modelClass) {
                $publishedCount += $modelClass::where('status', ResourceStatus::PUBLISHED)->count();
                $underReviewCount += $modelClass::where('status', ResourceStatus::UNDER_REVIEW)->count();
            }

            $today = Carbon::today();
            $searchVolume = SearchQuery::whereDate('created_at', $today)->count();
            $aiUsageToday = AiUsage::whereDate('created_at', $today)->count();

            return [
                'published_count' => $publishedCount,
                'under_review_count' => $underReviewCount,
                'search_volume_today' => $searchVolume,
                'ai_usage_today' => $aiUsageToday,
            ];
        });
    }

    public function getResourceGrowth(): array
    {
        $startDate = Carbon::now()->subDays(29)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $metrics = AnalyticsDailyMetric::where('resource_type', 'all')
            ->where('date', '>=', $startDate->toDateString())
            ->where('date', '<=', $endDate->toDateString())
            ->orderBy('date')
            ->get(['date', 'new_count']);

        $growth = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dateString = $current->toDateString();
            $metric = $metrics->first(fn ($m) => $m->date === $dateString || $m->date == $dateString);

            $growth[] = [
                'date' => $current->format('M d'),
                'count' => $metric ? $metric->new_count : 0,
            ];

            $current->addDay();
        }

        return $growth;
    }

    public function getSearchInsights(): array
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);

        $topSearches = SearchQuery::where('created_at', '>=', $sevenDaysAgo)
            ->where('results_count', '>', 0)
            ->select('query', DB::raw('COUNT(*) as count'))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $contentGaps = SearchQuery::where('created_at', '>=', $sevenDaysAgo)
            ->where('results_count', 0)
            ->select('query', DB::raw('COUNT(*) as count'))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return [
            'top_searches' => $topSearches,
            'content_gaps' => $contentGaps,
        ];
    }

    public function getAiUtilization(): array
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);

        $totals = AiUsage::where('created_at', '>=', $sevenDaysAgo)
            ->selectRaw('COUNT(*) as total_requests')
            ->selectRaw("SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_requests")
            ->selectRaw("SUM(CASE WHEN status IN ('failed', 'filtered') THEN 1 ELSE 0 END) as failed_requests")
            ->selectRaw('SUM(prompt_tokens + completion_tokens) as total_tokens')
            ->first();

        $totalRequests = (int) ($totals->total_requests ?? 0);
        $successfulRequests = (int) ($totals->successful_requests ?? 0);
        $failedRequests = (int) ($totals->failed_requests ?? 0);
        $totalTokens = (int) ($totals->total_tokens ?? 0);

        $successRate = $totalRequests > 0
            ? round(($successfulRequests / $totalRequests) * 100, 1)
            : 0;

        return [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'failed_requests' => $failedRequests,
            'success_rate' => $successRate,
            'total_tokens' => $totalTokens,
        ];
    }

    public function getPendingItems(int $limit = 20): array
    {
        $pending = [];

        foreach (self::RESOURCE_MODELS as $modelClass) {
            $items = $modelClass::query()
                ->whereIn('status', [ResourceStatus::DRAFT, ResourceStatus::UNDER_REVIEW])
                ->with(['categories'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            foreach ($items as $item) {
                $pending[] = $item;
            }
        }

        usort($pending, function ($a, $b) {
            return $b->created_at <=> $a->created_at;
        });

        return array_slice($pending, 0, $limit);
    }
}
