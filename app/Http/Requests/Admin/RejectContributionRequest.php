<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('contributions.review') ?? false;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
