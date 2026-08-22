<?php

namespace App\Http\Requests\Admin;

use App\Enums\ResourceStatus;
use App\Http\Requests\Admin\Concerns\ValidatesSeoEeat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreKnowledgeRequest extends FormRequest
{
    use ValidatesSeoEeat;

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('knowledge.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->input('slug') !== null && trim($this->input('slug')) !== ''
                ? Str::slug($this->input('slug'))
                : Str::slug($this->input('name', '')),
            'status' => $this->input('status', ResourceStatus::DRAFT->value),
            'reading_time_minutes' => $this->input('reading_time_minutes') !== null && trim((string) $this->input('reading_time_minutes')) === ''
                ? null
                : $this->input('reading_time_minutes'),
        ]);

        $this->cleanSeoEeatInput();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('knowledge_resources', 'slug')],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string'],
            'topic_category' => ['nullable', 'string', 'max:255'],
            'difficulty_level' => ['nullable', 'string', 'max:50', Rule::in(['Beginner', 'Intermediate', 'Advanced'])],
            'learning_objectives' => ['nullable', 'string'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'featured_image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
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
