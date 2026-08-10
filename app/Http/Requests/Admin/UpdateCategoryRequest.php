<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('taxonomy.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'slug' => app(CategoryService::class)->normalizeSlug($this->input('slug'), trim($this->input('name'))),
                'is_active' => $this->boolean('is_active'),
                'sort_order' => $this->input('sort_order') ?? 0,
            ]);
        }
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $category = $this->route('category');

            if (! $category instanceof Category) {
                return;
            }

            $parentId = $this->input('parent_id');

            if ($parentId !== null && app(CategoryService::class)->wouldCreateCycle($category->id, (int) $parentId)) {
                $validator->errors()->add('parent_id', 'The selected parent category would create a circular hierarchy.');
            }
        });
    }
}
