<?php

namespace App\Console\Commands;

use App\Mail\QueueHealthAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MonitorQueueHealth extends Command
{
    protected $signature = 'queue:health
        {--window= : Number of hours to look back. Defaults to the configured window.}
        {--failed-threshold= : Number of failed jobs that triggers an alert.}';

    protected $description = 'Alert admins when too many queued jobs have failed recently';

    public function handle(): int
    {
        $windowHours = (int) ($this->option('window')
            ?: config('labgoto.monitoring.queue_failed_window_hours', 24));
        $threshold = (int) ($this->option('failed-threshold')
            ?: config('labgoto.monitoring.queue_failed_threshold', 10));

        $alertEmail = (string) config('labgoto.security.alert_email', '');

        if ($alertEmail === '') {
            $this->warn('No alert email configured (labgoto.security.alert_email). Skipping.');

            return Command::SUCCESS;
        }

        $failedCount = DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHours($windowHours))
            ->count();

        if ($failedCount < $threshold) {
            $this->info("Queue health OK: {$failedCount} failed job(s) in the last {$windowHours}h.");

            return Command::SUCCESS;
        }

        // Alert at most once per day while the queue stays unhealthy.
        if (! Cache::add('queue-health-alert', true, 86400)) {
            $this->info('Queue alert already sent today; skipping.');

            return Command::SUCCESS;
        }

        Mail::to($alertEmail)->queue(new QueueHealthAlert(
            failedCount: $failedCount,
            windowHours: $windowHours,
        ));

        $this->alert("Queue health degraded: {$failedCount} failed job(s) in the last {$windowHours}h. Alert emailed to {$alertEmail}.");

        Log::warning('Queue health alert dispatched', [
            'failed_count' => $failedCount,
            'window_hours' => $windowHours,
            'threshold' => $threshold,
        ]);

        return Command::SUCCESS;
    }
}