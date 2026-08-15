<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKnowledgeContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'short_description' => ['required', 'string', 'min:10', 'max:300'],
            'content' => ['required', 'string', 'max:50000'],
            'learning_objectives' => ['nullable', 'string', 'max:2000'],
            'topic_category' => ['nullable', 'string', 'max:255'],
            'difficulty_level' => ['nullable', Rule::in(['Beginner', 'Intermediate', 'Advanced'])],
        ];
    }
}
