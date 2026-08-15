<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterpretationContributionRequest extends FormRequest
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
            'clinical_context' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
