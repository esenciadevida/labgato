<?php

namespace App\Console\Commands;

use App\Enums\ResourceStatus;
use App\Mail\WeeklyDigestEmail;
use App\Models\AnalyticsDailyMetric;
use App\Models\AuditLog;
use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\ReferenceRange;
use App\Models\Sop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigest extends Command
{
    protected $signature = 'digest:weekly {--days= : Number of days to summarize. Defaults to the configured window.}';

    protected $description = 'Email active administrators a weekly summary of platform activity';

    public function handle(): int
    {
        $days = (int) ($this->option('days')
            ?: config('labgoto.monitoring.digest_week_days', 7));

        $end = Carbon::today();
        $start = Carbon::today()->subDays(max(1, $days - 1));

        $recipients = $this->recipients();

        if ($recipients === []) {
            $this->warn('No digest recipients available (no alert email configured and no active administrators).');

            return Command::SUCCESS;
        }

        $metrics = $this->collectMetrics($start, $end);

        Mail::to($recipients)->queue(new WeeklyDigestEmail(
            weekStart: $start->toDateString(),
            weekEnd: $end->toDateString(),
            metrics: $metrics,
        ));

        $this->info('Weekly digest queued for '.count($recipients).' recipient(s).');

        Log::info('Weekly digest dispatched', [
            'recipients' => count($recipients),
            'week_start' => $start->toDateString(),
            'week_end' => $end->toDateString(),
        ]);

        return Command::SUCCESS;
    }

    private function recipients(): array
    {
        $configured = (string) config('labgoto.security.alert_email', '');

        if ($configured !== '') {
            return array_filter(array_map('trim', explode(',', $configured)));
        }

        return User::query()
            ->administrators()
            ->where('is_active', true)
            ->get()
            ->map(fn (User $user) => $user->email)
            ->filter()
            ->values()
            ->all();
    }

    private function collectMetrics(Carbon $start, Carbon $end): array
    {
        $rows = AnalyticsDailyMetric::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $overall = $rows->where('resource_type', 'all')->where('status', 'all')->values();

        $searchVolume = (int) $overall->sum('search_volume');
        $searchZeroResults = (int) $overall->sum('search_zero_results');
        $aiRequests = (int) $overall->sum('ai_requests_total');
        $aiFailed = (int) $overall->sum('ai_requests_failed');
        $aiTokens = (int) $overall->sum('ai_tokens_used');

        $newResources = (int) $rows
            ->where('resource_type', 'all')
            ->sum('new_count');

        $newMembers = User::query()
            ->users()
            ->whereBetween('created_at', [$start, $end->copy()->endOfDay()])
            ->count();

        $pendingContributions = $this->pendingContributions();

        $auditEvents = AuditLog::query()
            ->whereBetween('created_at', [$start, $end->copy()->endOfDay()])
            ->count();

        $failedJobs = DB::table('failed_jobs')
            ->whereBetween('failed_at', [$start, $end->copy()->endOfDay()])
            ->count();

        return [
            'Searches performed' => number_format($searchVolume),
            'Searches with no results' => number_format($searchZeroResults),
            'New members' => number_format($newMembers),
            'New resources published' => number_format($newResources),
            'Contributions awaiting review' => number_format($pendingContributions),
            'Audit events recorded' => number_format($auditEvents),
            'AI assistant requests' => number_format($aiRequests),
            'AI requests failed' => number_format($aiFailed),
            'AI tokens consumed' => number_format($aiTokens),
            'Failed background jobs' => number_format($failedJobs),
        ];
    }

    private function pendingContributions(): int
    {
        $models = [
            Calculator::class,
            ReferenceRange::class,
            InterpretationResource::class,
            KnowledgeResource::class,
            LaboratoryTool::class,
            Sop::class,
        ];

        $total = 0;

        foreach ($models as $model) {
            $total += $model::query()
                ->where('status', ResourceStatus::UNDER_REVIEW->value)
                ->whereNotNull('contributed_by')
                ->count();
        }

        return $total;
    }
}