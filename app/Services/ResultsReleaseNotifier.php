<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Notifications\ResultsReleasedNotification;

class ResultsReleaseNotifier
{
    /**
     * Notify every participant with a submitted official attempt that the
     * assessment's results are live. Idempotent: participants already holding
     * a release notification for this assessment are skipped.
     */
    public function notifyReleased(Assessment $assessment): int
    {
        $sent = 0;

        User::query()
            ->whereIn('id', $this->participantIds($assessment))
            ->chunkById(500, function ($users) use ($assessment, &$sent) {
                foreach ($users as $user) {
                    $exists = $user->notifications()
                        ->where('type', ResultsReleasedNotification::class)
                        ->where('data->assessment_id', $assessment->id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $attempt = ExamAttempt::query()
                        ->where('user_id', $user->id)
                        ->where('assessment_id', $assessment->id)
                        ->where('mode', 'official')
                        ->whereIn('status', ['submitted', 'auto_submitted'])
                        ->orderByDesc('id')
                        ->first();

                    if ($attempt === null) {
                        continue;
                    }

                    $user->notify(new ResultsReleasedNotification($assessment, $attempt));
                    $sent++;
                }
            });

        return $sent;
    }

    private function participantIds(Assessment $assessment): \Illuminate\Database\Eloquent\Builder
    {
        return ExamAttempt::query()
            ->select('user_id')
            ->where('assessment_id', $assessment->id)
            ->where('mode', 'official')
            ->whereIn('status', ['submitted', 'auto_submitted'])
            ->distinct();
    }
}
