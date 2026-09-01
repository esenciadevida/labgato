<?php

namespace App\Services\Analytics;

use App\Enums\ResourceStatus;
use App\Models\AiUsage;
use App\Models\AnalyticsDailyMetric;
use App\Models\AuditLog;
use App\Models\Calculator;
use App\Models\Certificate;
use App\Models\ExamAttempt;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\Question;
use App\Models\ReferenceRange;
use App\Models\SearchQuery;
use App\Models\Sop;
use App\Models\User;
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

    public function getUserMetrics(): array
    {
        return Cache::remember('analytics:today:users', 900, function () {
            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            $registrations = User::where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date')
                ->toArray();

            $dailyRegistrations = [];
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dateString = $current->toDateString();
                $dailyRegistrations[] = [
                    'date' => $current->format('M d'),
                    'count' => $registrations[$dateString] ?? 0,
                ];
                $current->addDay();
            }

            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            $onboardedUsers = User::whereNotNull('onboarded_at')->count();
            $onboardingRate = $totalUsers > 0 ? round(($onboardedUsers / $totalUsers) * 100, 1) : 0;
            $last7dActive = User::where('last_login_at', '>=', Carbon::now()->subDays(7))->count();

            return [
                'daily_registrations' => $dailyRegistrations,
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'onboarded_users' => $onboardedUsers,
                'onboarding_rate' => $onboardingRate,
                'last_7d_active' => $last7dActive,
            ];
        });
    }

    public function getExamMetrics(): array
    {
        return Cache::remember('analytics:today:exams', 900, function () {
            $submittedAttempts = ExamAttempt::whereIn('status', ['submitted', 'auto_submitted']);

            $totalAttempts = (clone $submittedAttempts)->count();
            $avgScore = (clone $submittedAttempts)->avg('percentage') ?? 0;
            $passCount = (clone $submittedAttempts)->where('percentage', '>=', 70)->count();
            $passRate = $totalAttempts > 0 ? round(($passCount / $totalAttempts) * 100, 1) : 0;

            $officialCount = ExamAttempt::where('mode', 'official')
                ->whereIn('status', ['submitted', 'auto_submitted'])->count();
            $practiceCount = ExamAttempt::where('mode', 'practice')
                ->whereIn('status', ['submitted', 'auto_submitted'])->count();

            $integrityReview = ExamAttempt::where('integrity_status', 'review')->count();
            $integrityHighRisk = ExamAttempt::where('integrity_status', 'high_risk')->count();

            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            $dailyScores = ExamAttempt::whereIn('status', ['submitted', 'auto_submitted'])
                ->where('submitted_at', '>=', $startDate)
                ->where('submitted_at', '<=', $endDate)
                ->selectRaw('DATE(submitted_at) as date, AVG(percentage) as avg_score, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $scoreTrend = [];
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dateString = $current->toDateString();
                $dayData = $dailyScores[$dateString] ?? null;
                $scoreTrend[] = [
                    'date' => $current->format('M d'),
                    'avg_score' => $dayData ? round($dayData->avg_score, 1) : null,
                    'count' => $dayData ? $dayData->count : 0,
                ];
                $current->addDay();
            }

            return [
                'total_attempts' => $totalAttempts,
                'avg_score' => round($avgScore, 1),
                'pass_rate' => $passRate,
                'official_count' => $officialCount,
                'practice_count' => $practiceCount,
                'integrity_review' => $integrityReview,
                'integrity_high_risk' => $integrityHighRisk,
                'score_trend' => $scoreTrend,
            ];
        });
    }

    public function getCertificateMetrics(): array
    {
        return Cache::remember('analytics:today:certs', 900, function () {
            $totalIssued = Certificate::count();
            $totalRevoked = Certificate::whereNotNull('revoked_at')->count();
            $avgScore = Certificate::avg('score_percent') ?? 0;

            $typeDistribution = Certificate::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();

            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            $dailyIssuance = Certificate::where('issued_at', '>=', $startDate)
                ->where('issued_at', '<=', $endDate)
                ->selectRaw('DATE(issued_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date')
                ->toArray();

            $issuanceTrend = [];
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dateString = $current->toDateString();
                $issuanceTrend[] = [
                    'date' => $current->format('M d'),
                    'count' => $dailyIssuance[$dateString] ?? 0,
                ];
                $current->addDay();
            }

            $typeLabels = array_map(fn ($t) => Certificate::LABELS[$t] ?? ucfirst($t), array_keys($typeDistribution));

            return [
                'total_issued' => $totalIssued,
                'total_revoked' => $totalRevoked,
                'avg_score' => round($avgScore, 1),
                'type_distribution' => array_values($typeDistribution),
                'type_labels' => $typeLabels,
                'issuance_trend' => $issuanceTrend,
            ];
        });
    }

    public function getQuestionBankHealth(): array
    {
        return Cache::remember('analytics:today:questions', 900, function () {
            $total = Question::count();

            $statusBreakdown = Question::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $difficultyBreakdown = Question::selectRaw('difficulty, COUNT(*) as count')
                ->groupBy('difficulty')
                ->pluck('count', 'difficulty')
                ->toArray();

            $typeBreakdown = Question::selectRaw('question_type, COUNT(*) as count')
                ->groupBy('question_type')
                ->pluck('count', 'question_type')
                ->toArray();

            $withImages = Question::whereIn('question_type', ['image', 'text_image'])->count();
            $imageQuestionsTotal = Question::whereNotNull('image_path')
                ->orWhereIn('question_type', ['image', 'text_image'])->count();
            $imageCoverage = $total > 0 ? round(($imageQuestionsTotal / $total) * 100, 1) : 0;

            $categoryCounts = Question::selectRaw('question_category_id, COUNT(*) as count')
                ->groupBy('question_category_id')
                ->orderByDesc('count')
                ->limit(10)
                ->with('category:id,name')
                ->get()
                ->map(fn ($q) => [
                    'name' => $q->category->name ?? 'Uncategorized',
                    'count' => $q->count,
                ])
                ->toArray();

            return [
                'total' => $total,
                'status_breakdown' => $statusBreakdown,
                'status_labels' => array_map(fn ($s) => ucfirst($s), array_keys($statusBreakdown)),
                'status_values' => array_values($statusBreakdown),
                'difficulty_breakdown' => $difficultyBreakdown,
                'difficulty_labels' => array_map(fn ($d) => ucfirst($d), array_keys($difficultyBreakdown)),
                'difficulty_values' => array_values($difficultyBreakdown),
                'type_breakdown' => $typeBreakdown,
                'type_labels' => array_map(fn ($t) => str_replace('_', ' ', ucfirst($t)), array_keys($typeBreakdown)),
                'type_values' => array_values($typeBreakdown),
                'with_images' => $withImages,
                'image_coverage' => $imageCoverage,
                'category_counts' => $categoryCounts,
            ];
        });
    }

    public function getRecentActivity(int $limit = 15): \Illuminate\Support\Collection
    {
        return AuditLog::with('user:id,name')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
