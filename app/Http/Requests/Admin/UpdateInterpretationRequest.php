<?php

namespace App\Http\Requests\Admin;

use App\Enums\ResourceStatus;
use App\Http\Requests\Admin\Concerns\ValidatesSeoEeat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateInterpretationRequest extends FormRequest
{
    use ValidatesSeoEeat;

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('interpretation.edit') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->input('slug') !== null && trim($this->input('slug')) !== ''
                ? Str::slug($this->input('slug'))
                : Str::slug($this->input('name', '')),
            'related_analytes' => collect(explode(',', (string) $this->input('related_analytes')))
                ->map(fn ($value) => trim($value))
                ->filter(fn ($value) => $value !== '')
                ->unique()
                ->values()
                ->all(),
        ]);

        $this->cleanSeoEeatInput();
    }

    public function rules(): array
    {
        $interpretation = $this->route('interpretation');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('interpretation_resources', 'slug')->ignore($interpretation->id)],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string'],
            'clinical_context' => ['nullable', 'string'],
            'related_analytes' => ['nullable', 'array'],
            'related_analytes.*' => ['string', 'max:100'],
            'mandatory_disclaimer' => ['required', 'string', 'max:5000'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'featured_image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'featured_image_url' => ['nullable', 'string', 'max:255', 'regex:/^https?:\/\/\S+$/i'],
            'reading_time_minutes' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'status' => ['required', Rule::enum(ResourceStatus::class)],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', Rule::exists('categories', 'id')],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', Rule::exists('tags', 'id')],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            ...$this->seoEeatRules(),
            'remove_featured_image' => ['nullable', 'boolean'],
        ];
    }
}
