<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateAssessmentRequest extends StoreAssessmentRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['slug'] = [
            'required',
            'string',
            'max:150',
            'alpha_dash',
            Rule::unique('assessments', 'slug')->ignore($this->route('assessment')->id ?? null),
        ];

        return $rules;
    }
}
