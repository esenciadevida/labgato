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
        $expired = ExamAttempt::query()
            ->where('status', 'in_progress')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->get();

        foreach ($expired as $attempt) {
            $scoringService->score($attempt, autoSubmitted: true);
        }

        if ($expired->isNotEmpty()) {
            $this->info("Auto-submitted {$expired->count()} expired attempt(s).");
        } else {
            $this->info('No expired attempts.');
        }

        Log::info('exam autosubmit tick', ['count' => $expired->count()]);

        return self::SUCCESS;
    }
}
