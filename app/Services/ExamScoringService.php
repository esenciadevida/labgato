<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\Cache;

class ExamScoringService
{
    /**
     * Score an attempt and mark it submitted (or auto_submitted).
     */
    public function score(ExamAttempt $attempt, bool $autoSubmitted = false): ExamAttempt
    {
        if ($attempt->status !== 'in_progress') {
            return $attempt;
        }

        $attempt->load(['answers', 'answers.selectedOption']);

        $correct = 0;
        $answered = 0;

        foreach ($attempt->answers as $answer) {
            if ($answer->selected_option_id === null) {
                if ($answer->is_correct !== null || $answer->answered_at !== null) {
                    $answer->forceFill(['is_correct' => null])->save();
                }

                continue;
            }

            $answered++;
            $isCorrect = (bool) QuestionOption::query()
                ->whereKey($answer->selected_option_id)
                ->where('is_correct', true)
                ->exists();

            $answer->is_correct = $isCorrect;
            $answer->answered_at ??= now();
            $answer->save();

            if ($isCorrect) {
                $correct++;
            }
        }

        $total = count($attempt->question_ids ?? []);
        $submittedAt = now();

        $timeSpent = null;

        if ($attempt->started_at !== null) {
            $durationCap = ($attempt->assessment->duration_minutes ?? 0) * 60;
            $spent = max(0, $attempt->started_at->diffInSeconds($submittedAt, false));
            $timeSpent = $durationCap > 0 ? min($durationCap, (int) round($spent)) : (int) round($spent);
        }

        $attempt->forceFill([
            'score' => $correct,
            'percentage' => $total > 0 ? round($correct / $total * 100, 2) : 0,
            'time_spent_seconds' => $timeSpent,
            'status' => $autoSubmitted ? 'auto_submitted' : 'submitted',
            'submitted_at' => $submittedAt,
        ])->save();

        Cache::forget("leaderboard:{$attempt->assessment_id}");
        Cache::forget('leaderboard:alltime');

        return $attempt->fresh();
    }
}
