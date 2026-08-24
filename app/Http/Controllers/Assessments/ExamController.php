<?php

namespace App\Http\Controllers\Assessments;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\ExamAttempt;
use App\Services\ExamEligibilityService;
use App\Services\ExamGenerationService;
use App\Services\ExamScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function __construct(
        protected ExamEligibilityService $eligibility,
        protected ExamGenerationService $generation,
        protected ExamScoringService $scoring,
    ) {
    }

    public function start(Request $request, Assessment $assessment)
    {
        $mode = $request->input('mode', 'official') === 'practice' ? 'practice' : 'official';

        $check = $this->eligibility->canStart($request->user(), $assessment, $mode);

        if (! $check['ok']) {
            if ($check['redirect'] !== null) {
                return redirect()
                    ->route('exams.show', $check['redirect'])
                    ->with('info', 'You already have an attempt for this challenge.');
            }

            return back()->with('error', $this->humanReason($check['reason']));
        }

        $attempt = $this->generation->startAttempt($request->user(), $assessment, $mode);

        return redirect()->route('exams.show', $attempt);
    }

    public function show(Request $request, ExamAttempt $attempt)
    {
        $this->authorizeAttempt($request, $attempt);

        if ($attempt->isExpired()) {
            $attempt = $this->scoring->score($attempt, autoSubmitted: true);
        }

        $attempt->load(['assessment']);

        return view('exam.show', [
            'attempt' => $attempt,
            'remainingSeconds' => $attempt->remainingSeconds(),
        ]);
    }

    public function saveAnswer(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($request, $attempt);

        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'option_id' => ['nullable', 'integer'],
        ]);

        if ($attempt->isExpired()) {
            $this->scoring->score($attempt, autoSubmitted: true);

            return response()->json([
                'message' => 'Time is up. Your exam was submitted automatically.',
                'expired' => true,
            ], 422);
        }

        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'message' => 'This attempt is already submitted.',
            ], 422);
        }

        $questionId = (int) $validated['question_id'];

        if (! $attempt->hasQuestion($questionId)) {
            return response()->json(['message' => 'Question not part of this attempt.'], 422);
        }

        $allowedOptionIds = array_map(intval(...), $attempt->optionIdsFor($questionId));

        $optionId = isset($validated['option_id']) ? (int) $validated['option_id'] : null;

        if ($optionId !== null && ! in_array($optionId, $allowedOptionIds, true)) {
            return response()->json(['message' => 'Invalid option for this question.'], 422);
        }

        $answer = $attempt->answers()->firstOrNew(['question_id' => $questionId]);
        $answer->selected_option_id = $optionId;
        $answer->answered_at = now();
        $answer->save();

        return response()->json([
            'saved' => true,
            'question_id' => $questionId,
            'option_id' => $optionId,
            'remaining_seconds' => $attempt->fresh()->remainingSeconds(),
        ]);
    }

    public function flag(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($request, $attempt);

        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
        ]);

        if ($attempt->status !== 'in_progress') {
            return response()->json(['message' => 'This attempt is already submitted.'], 422);
        }

        $questionId = (int) $validated['question_id'];

        if (! $attempt->hasQuestion($questionId)) {
            return response()->json(['message' => 'Question not part of this attempt.'], 422);
        }

        $answer = $attempt->answers()->firstOrCreate(['question_id' => $questionId]);
        $answer->flagged = ! $answer->flagged;
        $answer->save();

        return response()->json([
            'question_id' => $questionId,
            'flagged' => (bool) $answer->flagged,
        ]);
    }

    public function submit(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($request, $attempt);

        if ($attempt->status !== 'in_progress') {
            $attempt = $attempt->fresh();

            return response()->json($this->summary($attempt));
        }

        $auto = $attempt->isExpired();

        $attempt = $this->scoring->score($attempt, autoSubmitted: $auto);

        return response()->json($this->summary($attempt));
    }

    private function summary(ExamAttempt $attempt): array
    {
        return [
            'status' => $attempt->status,
            'submitted_at' => optional($attempt->submitted_at)->toIso8601String(),
            'score' => $attempt->score,
            'total_questions' => count($attempt->question_ids ?? []),
            'percentage' => $attempt->percentage !== null ? (float) $attempt->percentage : null,
            'time_spent_seconds' => $attempt->time_spent_seconds,
        ];
    }

    private function authorizeAttempt(Request $request, ExamAttempt $attempt): void
    {
        abort_unless((int) $attempt->user_id === (int) $request->user()?->id, 403);
    }

    private function humanReason(?string $reason): string
    {
        return match ($reason) {
            'already_attempted' => 'You have already used your attempt for this challenge.',
            'practice_disabled' => 'Practice mode is not available for this assessment.',
            'assessment_not_open' => 'This assessment is not open right now.',
            'exam_submission_closed' => 'Exam submissions are closed at the moment.',
            default => 'You cannot start this assessment.',
        };
    }
}
