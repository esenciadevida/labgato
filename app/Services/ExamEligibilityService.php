<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\ExamAttempt;
use App\Models\LaunchSetting;
use App\Models\User;

class ExamEligibilityService
{
    /**
     * Decide whether the user may start (or resume) an attempt.
     *
     * @return array{ok: bool, reason: ?string, redirect: ?ExamAttempt}
     */
    public function canStart(User $user, Assessment $assessment, string $mode = 'official'): array
    {
        if (! in_array($mode, ExamAttempt::MODES, true)) {
            return ['ok' => false, 'reason' => 'invalid_mode', 'redirect' => null];
        }

        if (! LaunchSetting::current()->challenge_open) {
            return ['ok' => false, 'reason' => 'exam_submission_closed', 'redirect' => null];
        }

        if (! $assessment->isOpen()) {
            return ['ok' => false, 'reason' => 'assessment_not_open', 'redirect' => null];
        }

        if ($mode === 'practice') {
            if (! $assessment->allow_practice) {
                return ['ok' => false, 'reason' => 'practice_disabled', 'redirect' => null];
            }

            return ['ok' => true, 'reason' => null, 'redirect' => null];
        }

        $existing = $this->existingOfficialAttempt($user, $assessment);

        if ($existing !== null) {
            return [
                'ok' => false,
                'reason' => 'already_attempted',
                'redirect' => $existing,
            ];
        }

        return ['ok' => true, 'reason' => null, 'redirect' => null];
    }

    public function existingOfficialAttempt(User $user, Assessment $assessment): ?ExamAttempt
    {
        return ExamAttempt::query()
            ->where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->where('mode', 'official')
            ->whereIn('status', ['in_progress', 'submitted', 'auto_submitted'])
            ->orderByDesc('attempt_number')
            ->first();
    }
}
