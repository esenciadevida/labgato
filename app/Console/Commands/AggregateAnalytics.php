<?php

namespace App\Console\Commands;

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
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AggregateAnalytics extends Command
{
    protected $signature = 'analytics:aggregate {--date= : The date to aggregate (YYYY-MM-DD). Defaults to yesterday.}';

    protected $description = 'Aggregate daily analytics metrics and purge logs older than 90 days';

    private const RESOURCE_TYPES = [
        'calculator' => Calculator::class,
        'laboratory_tool' => LaboratoryTool::class,
        'reference_range' => ReferenceRange::class,
        'sop' => Sop::class,
        'interpretation_resource' => InterpretationResource::class,
        'knowledge_resource' => KnowledgeResource::class,
    ];

    private const STATUSES = [
        ResourceStatus::DRAFT,
        ResourceStatus::UNDER_REVIEW,
        ResourceStatus::PUBLISHED,
        ResourceStatus::ARCHIVED,
        ResourceStatus::OBSOLETE,
    ];

    public function handle(): int
    {
        $targetDate = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $this->info("Aggregating analytics for: {$targetDate->toDateString()}");

        try {
            DB::transaction(function () use ($targetDate) {
                $this->aggregateResourceCounts($targetDate);
                $this->aggregateSearchMetrics($targetDate);
                $this->aggregateAiMetrics($targetDate);
            });

            $this->purgeOldLogs();

            $this->info('Analytics aggregation completed successfully.');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Analytics aggregation failed', [
                'date' => $targetDate->toDateString(),
                'error' => $e->getMessage(),
            ]);

            $this->error("Analytics aggregation failed: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    private function aggregateResourceCounts(Carbon $targetDate): void
    {
        $allTotals = [];

        foreach (self::RESOURCE_TYPES as $type => $modelClass) {
            foreach (self::STATUSES as $status) {
                $count = $modelClass::where('status', $status)->count();
                $newCount = $modelClass::where('status', $status)
                    ->whereDate('created_at', $targetDate)
                    ->count();

                AnalyticsDailyMetric::updateOrCreate(
                    [
                        'date' => $targetDate->toDateString(),
                        'resource_type' => $type,
                        'status' => $status->value,
                    ],
                    [
                        'total_count' => $count,
                        'new_count' => $newCount,
                    ]
                );

                $allTotals[$status->value] = ($allTotals[$status->value] ?? 0) + $count;
            }
        }

        foreach (self::STATUSES as $status) {
            $totalAll = $allTotals[$status->value] ?? 0;
            $newAll = 0;

            foreach (self::RESOURCE_TYPES as $modelClass) {
                $newAll += $modelClass::where('status', $status)
                    ->whereDate('created_at', $targetDate)
                    ->count();
            }

            AnalyticsDailyMetric::updateOrCreate(
                [
                    'date' => $targetDate->toDateString(),
                    'resource_type' => 'all',
                    'status' => $status->value,
                ],
                [
                    'total_count' => $totalAll,
                    'new_count' => $newAll,
                ]
            );
        }
    }

    private function aggregateSearchMetrics(Carbon $targetDate): void
    {
        $searchVolume = SearchQuery::whereDate('created_at', $targetDate)->count();
        $zeroResults = SearchQuery::whereDate('created_at', $targetDate)
            ->where('results_count', 0)
            ->count();

        AnalyticsDailyMetric::updateOrCreate(
            [
                'date' => $targetDate->toDateString(),
                'resource_type' => 'all',
                'status' => 'all',
            ],
            [
                'search_volume' => $searchVolume,
                'search_zero_results' => $zeroResults,
            ]
        );
    }

    private function aggregateAiMetrics(Carbon $targetDate): void
    {
        $aiRequestsTotal = AiUsage::whereDate('created_at', $targetDate)->count();
        $aiRequestsFailed = AiUsage::whereDate('created_at', $targetDate)
            ->whereIn('status', ['failed', 'filtered'])
            ->count();
        $aiTokensUsed = AiUsage::whereDate('created_at', $targetDate)
            ->sum(DB::raw('prompt_tokens + completion_tokens'));

        AnalyticsDailyMetric::updateOrCreate(
            [
                'date' => $targetDate->toDateString(),
                'resource_type' => 'all',
                'status' => 'all',
            ],
            [
                'ai_requests_total' => $aiRequestsTotal,
                'ai_requests_failed' => $aiRequestsFailed,
                'ai_tokens_used' => (int) $aiTokensUsed,
            ]
        );
    }

    private function purgeOldLogs(): void
    {
        $cutoffDate = Carbon::now()->subDays(90);

        $searchQueriesPurged = SearchQuery::where('created_at', '<', $cutoffDate)
            ->orderBy('id')
            ->chunkById(1000, function ($records) {
                $records->each->delete();
            });

        $aiUsagePurged = AiUsage::where('created_at', '<', $cutoffDate)
            ->orderBy('id')
            ->chunkById(1000, function ($records) {
                $records->each->delete();
            });

        $searchCount = SearchQuery::where('created_at', '<', $cutoffDate)->count();
        $aiCount = AiUsage::where('created_at', '<', $cutoffDate)->count();

        $this->info("Purged {$searchCount} old search queries and {$aiCount} old AI usage records.");

        Log::info('Analytics log purge completed', [
            'search_queries_purged' => $searchCount,
            'ai_usage_purged' => $aiCount,
        ]);
    }
}
