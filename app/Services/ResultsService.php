<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\ExamAttempt;
use App\Models\LaunchSetting;
use App\Models\Question;
use Illuminate\Support\Collection;

class ResultsService
{
    public function isReleased(Assessment $assessment): bool
    {
        if (LaunchSetting::current()->resultsAvailable()) {
            return true;
        }

        return ! $assessment->isLockedResults();
    }

    public function officialAttempts(Assessment $assessment)
    {
        return ExamAttempt::query()
            ->where('assessment_id', $assessment->id)
            ->where('mode', 'official')
            ->whereIn('status', ['submitted', 'auto_submitted']);
    }

    public function rank(ExamAttempt $attempt): int
    {
        $better = $this->officialAttempts($attempt->assessment)
            ->where('score', '>', $attempt->score)
            ->count();

        return 1 + $better;
    }

    public function topPercent(ExamAttempt $attempt): int
    {
        $total = $this->officialAttempts($attempt->assessment)->count();

        if ($total === 0) {
            return 100;
        }

        return (int) round(($this->rank($attempt) / $total) * 100);
    }

    public function summary(ExamAttempt $attempt): array
    {
        return [
            'score' => $attempt->score,
            'total' => count($attempt->question_ids ?? []),
            'percent' => $attempt->percentage !== null ? (float) $attempt->percentage : null,
            'rank' => $this->rank($attempt),
            'topPercent' => $this->topPercent($attempt),
            'integrity_status' => $attempt->integrity_status,
        ];
    }

    public function subjectBreakdown(ExamAttempt $attempt): Collection
    {
        $questionIds = array_map(intval(...), $attempt->question_ids ?? []);

        $questions = Question::query()
            ->with('category:id,name,sort_order')
            ->whereIn('id', $questionIds)
            ->get();

        $answers = $attempt->answers()
            ->whereIn('question_id', $questionIds)
            ->get()
            ->keyBy(fn ($answer) => (int) $answer->question_id);

        return $questions
            ->groupBy(fn (Question $question) => $question->category?->id ?? 0)
            ->map(function ($group, $categoryId) use ($answers) {
                $correct = $group->filter(fn (Question $question) => $answers[$question->id]?->is_correct)->count();
                $total = $group->count();
                $category = $group->first()->category;

                return [
                    'name' => $category?->name ?? 'General',
                    'sort_order' => $category?->sort_order ?? 999,
                    'correct' => $correct,
                    'total' => $total,
                    'percent' => $total > 0 ? (int) round(($correct / $total) * 100) : 0,
                ];
            })
            ->sortBy([['sort_order', 'asc'], ['name', 'asc']])
            ->values();
    }

    public function review(ExamAttempt $attempt): array
    {
        $questionIds = array_map(intval(...), $attempt->question_ids ?? []);

        $answers = $attempt->answers()
            ->get()
            ->keyBy(fn ($answer) => (int) $answer->question_id);

        $questions = Question::with(['options', 'category:id,name'])
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        return collect($questionIds)
            ->map(function (int $questionId, int $index) use ($attempt, $answers, $questions) {
                $question = $questions->get($questionId);

                if ($question === null) {
                    return null;
                }

                $frozenOrder = array_map(intval(...), $attempt->optionIdsFor($questionId));

                $lettersByOptionId = [];
                $optionsById = [];

                foreach ($frozenOrder as $position => $optionId) {
                    $letter = chr(65 + $position);
                    $lettersByOptionId[$optionId] = $letter;
                    $optionsById[$optionId] = $question->options->firstWhere('id', $optionId);
                }

                $correctOption = $question->options->firstWhere('is_correct', true);

                $answer = $answers->get($questionId);
                $selectedOptionId = $answer?->selected_option_id !== null ? (int) $answer->selected_option_id : null;

                return [
                    'number' => $index + 1,
                    'id' => $questionId,
                    'stem' => $question->stem,
                    'image_url' => $question->image_url,
                    'image_alt' => $question->image_alt,
                    'type' => $question->question_type,
                    'subject' => $question->category?->name,
                    'difficulty' => $question->difficulty,
                    'explanation' => $question->explanation,
                    'options' => collect($frozenOrder)
                        ->map(fn ($optionId, $position) => [
                            'letter' => chr(65 + $position),
                            'text' => $optionsById[$optionId]?->text,
                            'is_user_selection' => $optionId === $selectedOptionId,
                            'is_correct' => (bool) $optionsById[$optionId]?->is_correct,
                        ])
                        ->all(),
                    'your_option' => $selectedOptionId !== null && isset($lettersByOptionId[$selectedOptionId])
                        ? [
                            'label' => $optionsById[$selectedOptionId]?->label,
                            'text' => $optionsById[$selectedOptionId]?->text,
                            'letter' => $lettersByOptionId[$selectedOptionId],
                        ]
                        : null,
                    'correct_option' => $correctOption !== null
                        ? [
                            'label' => $correctOption->label,
                            'text' => $correctOption->text,
                            'letter' => $lettersByOptionId[(int) $correctOption->id] ?? '?',
                        ]
                        : null,
                    'is_correct' => (bool) ($answer?->is_correct),
                    'flagged' => (bool) ($answer?->flagged),
                ];
            })
            ->filter()
            ->all();
    }
}
