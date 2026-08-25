<?php

namespace App\Http\Controllers\Assessments;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\ExamAttempt;
use App\Models\LaunchSetting;
use App\Models\Question;
use App\Services\ExamEligibilityService;
use App\Services\ExamGenerationService;
use App\Services\ExamScoringService;
use App\Services\IntegrityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExamController extends Controller
{
    public function __construct(
        protected ExamEligibilityService $eligibility,
        protected ExamGenerationService $generation,
        protected ExamScoringService $scoring,
        protected IntegrityService $integrity,
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

        $attempt = $this->generation->startAttempt($request->user(), $assessment, $mode, $request);

        return redirect()->route('exams.show', $attempt);
    }

    public function showIntro(Request $request, Assessment $assessment)
    {
        $existing = ExamAttempt::query()
            ->where('user_id', $request->user()->id)
            ->where('assessment_id', $assessment->id)
            ->where('mode', 'official')
            ->orderByDesc('id')
            ->first();

        if ($existing && $existing->status === 'in_progress') {
            return redirect()->route('exams.show', $existing);
        }

        if ($existing && in_array($existing->status, ['submitted', 'auto_submitted'], true)) {
            return redirect()->route('exams.submitted', $existing);
        }

        $check = $this->eligibility->canStart($request->user(), $assessment, 'official');

        return view('assessments.show', [
            'assessment' => $assessment,
            'blueprints' => $assessment->blueprints()->with('category')->get(),
            'canBegin' => $check['ok'],
            'blockReason' => $this->humanReason($check['reason']),
            'canPractice' => $this->practiceAvailable($request, $assessment, null),
        ]);
    }

    public function show(Request $request, ExamAttempt $attempt)
    {
        $this->authorizeAttempt($request, $attempt);

        if ($attempt->status === 'in_progress') {
            $this->integrity->verifySession($attempt, $request);
        }

        if ($attempt->isExpired()) {
            $this->scoring->score($attempt, autoSubmitted: true);

            return redirect()->route('exams.submitted', $attempt);
        }

        if ($attempt->status === 'abandoned') {
            return redirect()->route('assessments.show', $attempt->assessment);
        }

        if (in_array($attempt->status, ['submitted', 'auto_submitted'], true)) {
            return redirect()->route('exams.submitted', $attempt);
        }

        $attempt->load(['assessment', 'answers']);

        $questionIds = array_map(intval(...), $attempt->question_ids ?? []);

        $questions = Question::with(['options', 'category'])
            ->whereIn('id', $questionIds)
            ->get()
            ->sortBy(fn (Question $q) => array_search($q->id, $questionIds, true))
            ->values()
            ->map(function (Question $question) use ($attempt) {
                $frozenOrder = array_map(intval(...), $attempt->optionIdsFor($question->id));

                $options = collect($frozenOrder)
                    ->map(fn ($optionId) => $question->options->firstWhere('id', $optionId))
                    ->filter()
                    ->values()
                    ->map(fn ($option, $index) => [
                        'id' => (int) $option->id,
                        'text' => $option->text,
                        'letter' => chr(65 + $index),
                    ])
                    ->all();

                return [
                    'id' => (int) $question->id,
                    'type' => $question->question_type,
                    'difficulty' => $question->difficulty,
                    'subject' => $question->category?->name,
                    'stem' => $question->stem,
                    'case_text' => $question->case_text,
                    'image_url' => $question->image_url,
                    'image_alt' => $question->image_alt,
                    'attribution' => $question->requiresAttribution() ? [
                        'source' => $question->image_source,
                        'license' => $question->image_license,
                        'attribution' => $question->image_attribution,
                        'source_url' => $question->image_source_url,
                    ] : null,
                    'options' => $options,
                ];
            })
            ->all();

        return view('assessments.exam', [
            'attempt' => $attempt,
            'questions' => collect($questions),
            'answersMap' => $attempt->answers
                ->filter(fn ($answer) => $answer->selected_option_id !== null)
                ->mapWithKeys(fn ($answer) => [(int) $answer->question_id => (int) $answer->selected_option_id])
                ->all(),
            'flagsMap' => $attempt->answers
                ->filter(fn ($answer) => $answer->flagged)
                ->mapWithKeys(fn ($answer) => [(int) $answer->question_id => true])
                ->all(),
            'remainingSeconds' => $attempt->remainingSeconds(),
        ]);
    }

    /**
     * Abandon an in-progress attempt (used by the Quit button and by the
     * integrity auto-cancel). Abandoned attempts do not count against the
     * member's limited attempts, so they can restart with a fresh random
     * question set.
     */
    public function cancel(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($request, $attempt);

        if ($attempt->status === 'in_progress') {
            $attempt->forceFill([
                'status' => 'abandoned',
                'submitted_at' => now(),
                'integrity_status' => 'review',
            ])->save();

            Cache::forget("leaderboard:{$attempt->assessment_id}");
            Cache::forget('leaderboard:alltime');
        }

        return response()->json([
            'status' => $attempt->fresh()->status,
            'redirect' => route('assessments.show', $attempt->assessment),
        ]);
    }

    public function submitted(Request $request, ExamAttempt $attempt)
    {
        $this->authorizeAttempt($request, $attempt);

        if ($attempt->status === 'in_progress') {
            return redirect()->route('exams.show', $attempt);
        }

        $attempt->load(['assessment.blueprints.category']);

        $released = LaunchSetting::current()->resultsAvailable()
            && ! $attempt->assessment->isLockedResults();

        return view('assessments.submitted', [
            'attempt' => $attempt,
            'released' => $released,
            'canPractice' => $this->practiceAvailable($request, $attempt->assessment, $attempt),
        ]);
    }

    /**
     * Practice runs unlock after the site is live, the assessment allows them
     * and is open, and the member has already banked an official attempt.
     */
    private function practiceAvailable(Request $request, Assessment $assessment, ?ExamAttempt $officialAttempt): bool
    {
        if (! LaunchSetting::current()->isLive() || ! LaunchSetting::current()->challenge_open) {
            return false;
        }

        if (! $assessment->allow_practice || ! $assessment->isOpen()) {
            return false;
        }

        if ($request->user() === null) {
            return false;
        }

        $submitted = $officialAttempt !== null
            ? in_array($officialAttempt->status, ['submitted', 'auto_submitted'], true) && $officialAttempt->mode === 'official'
            : ExamAttempt::query()
                ->where('user_id', $request->user()->id)
                ->where('assessment_id', $assessment->id)
                ->where('mode', 'official')
                ->whereIn('status', ['submitted', 'auto_submitted'])
                ->exists();

        return $submitted;
    }

    public function saveAnswer(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($request, $attempt);
        $this->integrity->verifySession($attempt, $request);

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
        $this->integrity->verifySession($attempt, $request);

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
        $this->integrity->verifySession($attempt, $request);

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
