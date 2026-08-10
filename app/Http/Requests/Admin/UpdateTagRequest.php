<?php

namespace App\Http\Requests\Admin;

use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('taxonomy.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $name = Tag::normalizeName($this->input('name'));

            $this->merge([
                'name' => $name,
                'slug' => $this->input('slug') !== null && trim($this->input('slug')) !== ''
                    ? Str::slug($this->input('slug'))
                    : Str::slug($name),
            ]);
        }
    }

    public function rules(): array
    {
        $tag = $this->route('tag');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')
                    ->where(fn ($query) => $query->whereRaw('LOWER(name) = ?', [Str::lower($this->input('name'))]))
                    ->ignore($tag?->id),
            ],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('tags', 'slug')->ignore($tag?->id)],
        ];
    }
}
