<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentBlueprint;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamGenerationService
{
    public function __construct(
        protected ExamEligibilityService $eligibility,
        protected IntegrityService $integrity,
    ) {
    }

    /**
     * Start (or return the already-frozen) attempt for this user + assessment.
     */
    public function startAttempt(User $user, Assessment $assessment, string $mode = 'official', ?Request $request = null): ExamAttempt
    {
        if ($mode === 'official' && $assessment->max_attempts === 1) {
            $existing = $this->eligibility->existingOfficialAttempt($user, $assessment);

            if ($existing !== null) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($user, $assessment, $mode, $request) {
            if ($mode === 'official' && $assessment->max_attempts === 1) {
                $race = $this->eligibility->existingOfficialAttempt($user, $assessment);

                if ($race !== null) {
                    return $race;
                }
            }

            $attemptNumber = (int) ExamAttempt::query()
                ->where('user_id', $user->id)
                ->where('assessment_id', $assessment->id)
                ->max('attempt_number') + 1;

            [$questionIds, $optionOrders] = $this->buildFrozenSets($assessment);

            $startedAt = now();

            $attempt = ExamAttempt::create([
                'user_id' => $user->id,
                'assessment_id' => $assessment->id,
                'attempt_number' => $attemptNumber,
                'mode' => $mode,
                'status' => 'in_progress',
                'question_ids' => $questionIds,
                'option_orders' => $optionOrders,
                'session_fingerprint' => $request !== null ? $this->integrity->fingerprint($request) : null,
                'started_at' => $startedAt,
                'ends_at' => $startedAt->copy()->addMinutes($assessment->duration_minutes),
            ]);

            foreach ($questionIds as $questionId) {
                $attempt->answers()->create([
                    'question_id' => $questionId,
                ]);
            }

            return $attempt;
        });
    }

    /**
     * Blueprint-driven, frozen question set + per-question option display order.
     *
     * @return array{0: list<int>, 1: array<string, list<int>>}
     */
    private function buildFrozenSets(Assessment $assessment): array
    {
        $blueprints = AssessmentBlueprint::query()
            ->where('assessment_id', $assessment->id)
            ->with('category')
            ->get();

        $selected = [];

        foreach ($blueprints as $blueprint) {
            $total = max(0, $blueprint->question_count);
            $easyN = (int) round($total * $blueprint->easy_percent / 100);
            $advN = (int) round($total * $blueprint->advanced_percent / 100);
            $interN = max(0, $total - $easyN - $advN);

            $categoryId = $blueprint->question_category_id;

            $picked = [
                ...$this->pick($categoryId, 'easy', $easyN, $selected),
                ...$this->pick($categoryId, 'intermediate', $interN, $selected),
                ...$this->pick($categoryId, 'advanced', $advN, $selected),
            ];

            // Shortfall: any active question in the same category.
            if (count($picked) < $total) {
                $picked = [...$picked, ...$this->pickAnyInCategory($categoryId, $total - count($picked), [...$selected, ...$picked])];
            }

            // Category-wide backfill: pull from other categories to reach the total.
            if (count($picked) < $total) {
                $picked = [...$picked, ...$this->pickGlobal($total - count($picked), [...$selected, ...$picked])];
            }

            $selected = [...$selected, ...$picked];
        }

        if ($assessment->shuffle_questions) {
            shuffle($selected);
        }

        $optionOrders = [];

        $questions = Question::query()
            ->with('options:id,question_id,is_correct,sort_order')
            ->whereIn('id', $selected)
            ->get(['id']);

        foreach ($selected as $questionId) {
            $optionIds = $questions->find($questionId)?->options
                ->sortBy('sort_order')
                ->pluck('id')
                ->values()
                ->all() ?? [];

            if ($assessment->shuffle_options) {
                shuffle($optionIds);
            }

            $optionOrders[$questionId] = array_map(intval(...), $optionIds);
        }

        return [array_map(intval(...), $selected), $optionOrders];
    }

    /**
     * @return list<int>
     */
    private function pick(int $categoryId, string $difficulty, int $count, array $exclude): array
    {
        if ($count <= 0) {
            return [];
        }

        return Question::query()
            ->where('question_category_id', $categoryId)
            ->where('difficulty', $difficulty)
            ->active()
            ->whereNotIn('id', $exclude)
            ->inRandomOrder()
            ->limit($count)
            ->pluck('id')
            ->all();
    }

    /**
     * @return list<int>
     */
    private function pickAnyInCategory(int $categoryId, int $count, array $exclude): array
    {
        if ($count <= 0) {
            return [];
        }

        return Question::query()
            ->where('question_category_id', $categoryId)
            ->active()
            ->whereNotIn('id', $exclude)
            ->inRandomOrder()
            ->limit($count)
            ->pluck('id')
            ->all();
    }

    /**
     * @return list<int>
     */
    private function pickGlobal(int $count, array $exclude): array
    {
        if ($count <= 0) {
            return [];
        }

        return Question::query()
            ->active()
            ->whereNotIn('id', $exclude)
            ->inRandomOrder()
            ->limit($count)
            ->pluck('id')
            ->all();
    }
}
