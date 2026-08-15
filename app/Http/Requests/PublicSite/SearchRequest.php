<?php

namespace App\Http\Requests\PublicSite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('q')) {
            $q = trim(strip_tags((string) $this->input('q')));
            $this->merge([
                'q' => $q === '' ? null : $q,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', Rule::in(['all', 'calculator', 'tool', 'reference_range', 'interpretation', 'knowledge', 'sop'])],
            'category' => ['nullable', 'string', 'max:255', Rule::exists('categories', 'slug')->where('is_active', true)],
            'tag' => ['nullable', 'string', 'max:255', Rule::exists('tags', 'slug')],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
