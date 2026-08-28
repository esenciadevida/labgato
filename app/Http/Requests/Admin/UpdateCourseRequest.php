<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateCourseRequest extends StoreCourseRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['slug'] = [
            'required',
            'string',
            'max:150',
            'alpha_dash',
            Rule::unique('courses', 'slug')->ignore($this->route('course')->id ?? null),
        ];

        return $rules;
    }
}