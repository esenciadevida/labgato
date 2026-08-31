<?php

namespace App\Http\Requests\Admin;

use App\Models\Assessment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug');

        $this->merge([
            'slug' => filled($slug) ? Str::slug($slug) : Str::slug((string) $this->input('title')),
        ]);

        $blueprints = collect($this->input('blueprints', []))
            ->filter(fn ($row) => is_array($row))
            ->map(fn ($row, $index) => [
                'question_category_id' => $row['question_category_id'] ?? null,
                'include' => (int) filled($row['include'] ?? null),
                'question_count' => (int) ($row['question_count'] ?? 0),
                'easy_percent' => (int) ($row['easy_percent'] ?? 0),
                'intermediate_percent' => (int) ($row['intermediate_percent'] ?? 0),
                'advanced_percent' => (int) ($row['advanced_percent'] ?? 0),
            ])
            ->values()
            ->all();

        $this->merge(['blueprints' => $blueprints]);

        $planned = collect($this->input('blueprints', []))
            ->filter(fn ($row) => (int) ($row['include'] ?? 0) === 1)
            ->sum('question_count');

        // Auto mode (default): total is derived from blueprint counts. Manual mode
        // (auto_total = 0) lets the admin set an explicit total_questions override.
        $autoTotal = ! $this->has('auto_total') || $this->boolean('auto_total');

        if ($autoTotal) {
            $this->merge([
                'total_questions' => max(1, min(500, (int) $planned)),
            ]);
        } else {
            $this->merge([
                'total_questions' => max(1, min(500, (int) $this->input('total_questions', 10))),
            ]);
        }

        // Per-assessment certificate overrides: blank means "inherit global".
        $plainFields = [
            'cert_body_text',
            'cert_disclaimer_text',
            'cert_primary_color',
            'cert_secondary_color',
            'cert_signatory_name',
            'cert_signatory_title',
        ];

        foreach ($plainFields as $field) {
            $value = $this->input($field);

            if ($value === null || trim((string) $value) === '') {
                $this->merge([$field => null]);
            }
        }

        $this->merge([
            'cert_show_logo' => $this->nullableBoolean('cert_show_logo'),
            'cert_show_qr' => $this->nullableBoolean('cert_show_qr'),
        ]);
    }

    private function nullableBoolean(string $field): ?bool
    {
        $value = $this->input($field);

        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return $this->boolean($field);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', Rule::unique('assessments', 'slug')],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::in(Assessment::TYPES)],
            'status' => ['required', Rule::in(Assessment::STATUSES)],
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:600'],
            'total_questions' => ['required', 'integer', 'min:1', 'max:500'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:opens_at'],
            'pass_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'one_attempt_only' => ['nullable', 'boolean'],
            'allow_practice' => ['nullable', 'boolean'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'shuffle_options' => ['nullable', 'boolean'],
            'fullscreen_required' => ['nullable', 'boolean'],
            'certificate_enabled' => ['nullable', 'boolean'],
            'cert_body_text' => ['nullable', 'string', 'max:2000'],
            'cert_disclaimer_text' => ['nullable', 'string', 'max:2000'],
            'cert_primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'cert_secondary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'cert_signatory_name' => ['nullable', 'string', 'max:150'],
            'cert_signatory_title' => ['nullable', 'string', 'max:150'],
            'cert_show_logo' => ['nullable', 'boolean'],
            'cert_show_qr' => ['nullable', 'boolean'],
            'results_available' => ['nullable', 'boolean'],
            'answer_review_available' => ['nullable', 'boolean'],
            'leaderboard_visible' => ['nullable', 'boolean'],
            'blueprints' => ['nullable', 'array'],
            'blueprints.*.question_category_id' => ['nullable', 'integer', 'exists:question_categories,id', 'distinct'],
            'blueprints.*.include' => ['nullable', 'boolean'],
            'blueprints.*.question_count' => ['nullable', 'integer', 'min:0', 'max:500'],
            'blueprints.*.easy_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'blueprints.*.intermediate_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'blueprints.*.advanced_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $included = collect($this->input('blueprints', []))
                ->filter(fn (array $row) => (int) $row['include'] === 1);

            foreach ($included as $index => $row) {
                $sum = (int) $row['easy_percent'] + (int) $row['intermediate_percent'] + (int) $row['advanced_percent'];

                if ($sum !== 100) {
                    $categoryId = $row['question_category_id'];

                    $validator->errors()->add(
                        "blueprints.{$index}.easy_percent",
                        "Difficulty percentages for \"{$this->categoryName($categoryId)}\" must sum to 100 (currently {$sum})."
                    );
                }
            }

            $autoTotal = ! $this->has('auto_total') || $this->boolean('auto_total');

            if ($autoTotal) {
                $planned = (int) $included->sum('question_count');
                $total = (int) $this->input('total_questions');

                if ($planned !== $total) {
                    $validator->errors()->add(
                        'blueprints',
                        "Blueprint question counts add up to {$planned}, but the assessment expects {$total}."
                    );
                }
            }
        });
    }

    private function categoryName(?int $categoryId): string
    {
        return (string) \App\Models\QuestionCategory::find($categoryId)?->name ?? 'unknown subject';
    }

    /**
     * Toggles and status flags mapped onto the real column names.
     *
     * @return array<string, mixed>
     */
    public function assessmentAttributes(): array
    {
        $statusFlag = fn (bool $on) => $on ? 'available' : 'locked';

        return [
            'title' => $this->input('title'),
            'slug' => $this->input('slug'),
            'description' => $this->input('description'),
            'type' => $this->input('type'),
            'status' => $this->input('status'),
            'duration_minutes' => (int) $this->input('duration_minutes'),
            'total_questions' => (int) $this->input('total_questions'),
            'opens_at' => $this->filled('opens_at') ? $this->date('opens_at') : null,
            'closes_at' => $this->filled('closes_at') ? $this->date('closes_at') : null,
            'pass_percentage' => $this->filled('pass_percentage') ? $this->input('pass_percentage') : null,
            'max_attempts' => $this->boolean('one_attempt_only') ? 1 : 2,
            'allow_practice' => $this->boolean('allow_practice'),
            'shuffle_questions' => $this->boolean('shuffle_questions'),
            'shuffle_options' => $this->boolean('shuffle_options'),
            'fullscreen_required' => $this->boolean('fullscreen_required'),
            'certificate_enabled' => $this->boolean('certificate_enabled'),
            'cert_body_text' => $this->input('cert_body_text'),
            'cert_disclaimer_text' => $this->input('cert_disclaimer_text'),
            'cert_primary_color' => $this->input('cert_primary_color'),
            'cert_secondary_color' => $this->input('cert_secondary_color'),
            'cert_signatory_name' => $this->input('cert_signatory_name'),
            'cert_signatory_title' => $this->input('cert_signatory_title'),
            'cert_show_logo' => $this->input('cert_show_logo'),
            'cert_show_qr' => $this->input('cert_show_qr'),
            'results_status' => $statusFlag($this->boolean('results_available')),
            'answer_review_status' => $statusFlag($this->boolean('answer_review_available')),
            'leaderboard_status' => $statusFlag($this->boolean('leaderboard_visible')),
        ];
    }

    /** @return array<int, array<string, int>> */
    public function includedBlueprints(): array
    {
        return collect($this->input('blueprints', []))
            ->filter(fn (array $row) => (int) $row['include'] === 1)
            ->map(fn (array $row) => [
                'question_category_id' => (int) $row['question_category_id'],
                'question_count' => max(0, (int) $row['question_count']),
                'easy_percent' => (int) $row['easy_percent'],
                'intermediate_percent' => (int) $row['intermediate_percent'],
                'advanced_percent' => (int) $row['advanced_percent'],
            ])
            ->values()
            ->all();
    }
}
