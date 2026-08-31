<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Services\ExamScoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoSubmitExams extends Command
{
    protected $signature = 'exam:autosubmit';

    protected $description = 'Auto-submit exam attempts whose server timer has expired';

    public function handle(ExamScoringService $scoringService): int
    {
        $count = 0;
        $failed = 0;

        ExamAttempt::query()
            ->where('status', 'in_progress')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->orderBy('id')
            ->chunkById(50, function ($attempts) use ($scoringService, &$count, &$failed) {
                foreach ($attempts as $attempt) {
                    try {
                        $scoringService->score($attempt, autoSubmitted: true);
                        $count++;
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::error('exam autosubmit failed for attempt', [
                            'attempt_id' => $attempt->id,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
            });

        if ($count > 0) {
            $this->info("Auto-submitted {$count} expired attempt(s).");
        } else {
            $this->info('No expired attempts.');
        }

        if ($failed > 0) {
            $this->warn("{$failed} attempt(s) could not be auto-submitted; see the log.");
        }

        Log::info('exam autosubmit tick', ['count' => $count, 'failed' => $failed]);

        return self::SUCCESS;
    }
}
