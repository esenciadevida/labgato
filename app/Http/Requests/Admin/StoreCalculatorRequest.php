<?php

namespace App\Http\Requests\Admin;

use App\Enums\ResourceStatus;
use App\Rules\ValidResourceSchema;
use App\Services\Calculators\CalculatorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCalculatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('calculators.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'slug' => $this->input('slug') !== null && trim($this->input('slug')) !== ''
                    ? Str::slug($this->input('slug'))
                    : Str::slug($this->input('name')),
                'status' => $this->input('status', ResourceStatus::DRAFT->value),
            ]);
        }
    }

    public function rules(): array
    {
        $engineReferences = array_keys(app(CalculatorService::class)->availableEngines());

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('calculators', 'slug')],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'formula_display' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::enum(ResourceStatus::class)],
            'input_schema' => ['required', 'string', new ValidResourceSchema('input')],
            'output_schema' => ['required', 'string', new ValidResourceSchema('output')],
            'engine' => ['required', 'string', Rule::in(['php_class'])],
            'engine_reference' => ['required', 'string', Rule::in($engineReferences)],
            'interpretation_notes' => ['nullable', 'string', 'max:5000'],
            'references' => ['nullable', 'string', 'max:5000'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', Rule::exists('categories', 'id')],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', Rule::exists('tags', 'id')],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
        ];
    }
}
