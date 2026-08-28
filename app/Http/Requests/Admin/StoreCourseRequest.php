<?php

namespace App\Http\Requests\Admin;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
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

        $assessments = collect($this->input('assessments', []))
            ->filter(fn ($row) => is_array($row))
            ->map(fn ($row, $index) => [
                'assessment_id' => $row['assessment_id'] ?? null,
                'include' => (int) ($row['include'] ?? 0),
                'required' => (bool) ($row['required'] ?? false),
                'sort_order' => (int) ($row['sort_order'] ?? $index),
            ])
            ->values()
            ->all();

        $this->merge(['assessments' => $assessments]);

        // Certificate overrides: blank means "inherit global".
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
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', Rule::unique('courses', 'slug')],
            'summary' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::in(Course::STATUSES)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:32000'],
            'certificate_enabled' => ['nullable', 'boolean'],
            'certificate_requires_all' => ['nullable', 'boolean'],
            'cert_body_text' => ['nullable', 'string', 'max:2000'],
            'cert_disclaimer_text' => ['nullable', 'string', 'max:2000'],
            'cert_primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'cert_secondary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'cert_signatory_name' => ['nullable', 'string', 'max:150'],
            'cert_signatory_title' => ['nullable', 'string', 'max:150'],
            'cert_show_logo' => ['nullable', 'boolean'],
            'cert_show_qr' => ['nullable', 'boolean'],
            'assessments' => ['nullable', 'array'],
            'assessments.*.assessment_id' => ['nullable', 'integer', 'exists:assessments,id'],
            'assessments.*.required' => ['nullable', 'boolean'],
            'assessments.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:32000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function courseAttributes(): array
    {
        return [
            'title' => $this->input('title'),
            'slug' => $this->input('slug'),
            'summary' => $this->input('summary'),
            'description' => $this->input('description'),
            'status' => $this->input('status'),
            'sort_order' => (int) $this->input('sort_order', 0),
            'certificate_enabled' => $this->boolean('certificate_enabled'),
            'certificate_requires_all' => $this->boolean('certificate_requires_all'),
            'cert_body_text' => $this->input('cert_body_text'),
            'cert_disclaimer_text' => $this->input('cert_disclaimer_text'),
            'cert_primary_color' => $this->input('cert_primary_color'),
            'cert_secondary_color' => $this->input('cert_secondary_color'),
            'cert_signatory_name' => $this->input('cert_signatory_name'),
            'cert_signatory_title' => $this->input('cert_signatory_title'),
            'cert_show_logo' => $this->input('cert_show_logo'),
            'cert_show_qr' => $this->input('cert_show_qr'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function courseAssessments(): array
    {
        return collect($this->input('assessments', []))
            ->filter(fn (array $row) => (int) ($row['include'] ?? 0) === 1)
            ->map(fn (array $row) => [
                'assessment_id' => (int) $row['assessment_id'],
                'required' => (bool) $row['required'],
                'sort_order' => (int) $row['sort_order'],
            ])
            ->values()
            ->all();
    }
}