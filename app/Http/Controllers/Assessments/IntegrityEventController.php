<?php

namespace App\Http\Controllers\Assessments;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\IntegrityEvent;
use App\Services\IntegrityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrityEventController extends Controller
{
    public function __construct(protected IntegrityService $integrity)
    {
    }

    public function store(Request $request, ExamAttempt $attempt): JsonResponse
    {
        abort_unless((int) $attempt->user_id === (int) $request->user()?->id, 403);

        if ($attempt->status !== 'in_progress') {
            return response()->json(['message' => 'This attempt is no longer in progress.'], 422);
        }

        $events = $request->input('events');

        // Tolerate a single event object: {type, q, meta}
        if ($events === null && $request->input('type') !== null) {
            $events = [$request->all()];
        }

        if (! is_array($events) || $events === []) {
            return response()->json(['message' => 'No events provided.'], 422);
        }

        $events = array_slice($events, 0, 50);

        foreach ($events as $event) {
            $type = is_array($event) ? ($event['type'] ?? null) : null;

            if ($type === null || ! in_array($type, IntegrityEvent::TYPES, true)) {
                return response()->json([
                    'message' => 'Invalid event type.',
                    'invalid_type' => $type,
                ], 422);
            }
        }

        foreach ($events as $event) {
            $this->integrity->log(
                $attempt,
                $event['type'],
                isset($event['q']) ? (int) $event['q'] : null,
                is_array($event['meta'] ?? null) ? $event['meta'] : null,
            );
        }

        $cancelled = $this->cancelIfThresholdReached($attempt);

        return response()->json([
            'cancelled' => $cancelled,
            'redirect' => $cancelled ? route('assessments.show', $attempt->assessment) : null,
        ]);
    }

    /**
     * Auto-cancel an in-progress attempt once the member has left the page too
     * many times. Leaving/refreshing the page is treated as an integrity risk:
     * the attempt is abandoned so they have to restart with a fresh random
     * question set.
     */
    private function cancelIfThresholdReached(ExamAttempt $attempt): bool
    {
        if ($attempt->status !== 'in_progress') {
            return false;
        }

        $seriousEvents = IntegrityEvent::where('exam_attempt_id', $attempt->id)
            ->whereIn('event_type', ['tab_switch', 'fullscreen_exit'])
            ->count();

        $threshold = (int) config('labgoto.integrity.auto_cancel_threshold', 2);

        if ($seriousEvents < $threshold) {
            return false;
        }

        $attempt->forceFill([
            'status' => 'abandoned',
            'submitted_at' => now(),
            'integrity_status' => 'high_risk',
        ])->save();

        return true;
    }
}
