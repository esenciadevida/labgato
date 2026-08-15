<?php

namespace App\Http\Requests\Admin;

use App\Enums\ResourceStatus;
use App\Rules\ValidReferenceLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreReferenceRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('reference-ranges.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->input('slug') !== null && trim($this->input('slug')) !== ''
                ? Str::slug($this->input('slug'))
                : Str::slug($this->input('name', '')),
            'sex' => $this->input('sex', 'any'),
            'status' => $this->input('status', ResourceStatus::DRAFT->value),
        ]);

        foreach (['age_min', 'age_max', 'lower_limit', 'upper_limit', 'critical_low', 'critical_high', 'conversion_factor'] as $field) {
            if ($this->has($field) && $this->input($field) !== null && trim((string) $this->input($field)) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('reference_ranges', 'slug')],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'analyte_name' => ['required', 'string', 'max:255'],
            'parameter_key' => ['nullable', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'specimen_type' => ['nullable', 'string', 'max:255'],
            'methodology' => ['nullable', 'string', 'max:255'],
            'population_group' => ['nullable', 'string', 'max:255'],
            'age_min' => ['nullable', 'numeric', 'min:0', 'max:150'],
            'age_max' => ['nullable', 'numeric', 'min:0', 'max:150'],
            'sex' => ['required', 'string', 'max:20', Rule::in(['any', 'male', 'female'])],
            'unit' => ['required', 'string', 'max:50', new ValidReferenceLimits],
            'lower_limit' => ['nullable', 'numeric'],
            'upper_limit' => ['nullable', 'numeric'],
            'critical_low' => ['nullable', 'numeric'],
            'critical_high' => ['nullable', 'numeric'],
            'conversion_factor' => ['nullable', 'numeric'],
            'source_citation' => ['nullable', 'string', 'max:5000'],
            'effective_date' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(ResourceStatus::class)],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', Rule::exists('categories', 'id')],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', Rule::exists('tags', 'id')],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
        ];
    }
}
