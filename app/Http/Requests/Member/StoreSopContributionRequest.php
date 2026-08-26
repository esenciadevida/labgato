<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class StoreSopContributionRequest extends FormRequest
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
            'purpose' => ['required', 'string', 'max:5000'],
            'scope' => ['required', 'string', 'max:5000'],
            'principle' => ['nullable', 'string', 'max:5000'],
            'procedure_content' => ['required', 'string', 'max:50000'],
            'quality_control' => ['nullable', 'string', 'max:5000'],
            'safety_information' => ['nullable', 'string', 'max:5000'],
            'references' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
