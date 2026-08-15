<?php

namespace App\Http\Requests\Admin;

use App\Enums\ResourceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreSopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('sops.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->input('slug') !== null && trim($this->input('slug')) !== ''
                ? Str::slug($this->input('slug'))
                : Str::slug($this->input('name', '')),
            'status' => $this->input('status', ResourceStatus::DRAFT->value),
            'reviewed_date' => $this->input('reviewed_date') !== null && trim((string) $this->input('reviewed_date')) === ''
                ? null
                : $this->input('reviewed_date'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('sops', 'slug')],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string'],
            'department' => ['nullable', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:50'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'reviewed_date' => ['nullable', 'date'],
            'mandatory_disclaimer' => ['nullable', 'string', 'max:5000'],
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
