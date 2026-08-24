<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\IntegrityEvent;
use Illuminate\Http\Request;

class IntegrityService
{
    public function fingerprint(Request $request): string
    {
        return sha1($request->ip() . '|' . $request->userAgent());
    }

    public function log(ExamAttempt $attempt, string $type, ?int $questionNumber = null, ?array $metadata = null): IntegrityEvent
    {
        $event = IntegrityEvent::create([
            'exam_attempt_id' => $attempt->id,
            'user_id' => $attempt->user_id,
            'event_type' => $type,
            'question_number' => $questionNumber,
            'weight' => IntegrityEvent::weightFor($type),
            'metadata' => $metadata,
        ]);

        $this->recalculate($attempt);

        return $event;
    }

    public function recalculate(ExamAttempt $attempt): void
    {
        $total = (int) IntegrityEvent::where('exam_attempt_id', $attempt->id)->sum('weight');

        if ($total < 3) {
            $status = 'normal';
        } elseif ($total <= 7) {
            $status = 'review';
        } else {
            $status = 'high_risk';
        }

        if (IntegrityEvent::where('exam_attempt_id', $attempt->id)
            ->where('event_type', 'multiple_session')
            ->exists()) {
            $status = 'high_risk';
        }

        $attempt->forceFill(['integrity_status' => $status])->save();
    }

    public function verifySession(ExamAttempt $attempt, Request $request): void
    {
        if ($attempt->status !== 'in_progress') {
            return;
        }

        if ($attempt->session_fingerprint === null) {
            return;
        }

        if (hash_equals($attempt->session_fingerprint, $this->fingerprint($request))) {
            return;
        }

        $alreadyLogged = IntegrityEvent::where('exam_attempt_id', $attempt->id)
            ->where('event_type', 'session_change')
            ->exists();

        if (! $alreadyLogged) {
            $this->log($attempt, 'session_change', null, [
                'expected' => $attempt->session_fingerprint,
                'actual' => $this->fingerprint($request),
            ]);
        }
    }
}
