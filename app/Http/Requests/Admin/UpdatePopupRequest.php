<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('popups.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150', 'not_regex:/^\s*$/'],
            'content' => ['required', 'string', 'max:2000', 'not_regex:/^\s*$/'],
            'cta_text' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'string', 'max:500', 'url'],
            'trigger_type' => ['required', 'in:on_load,after_delay'],
            'delay_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'is_active' => ['nullable', 'boolean'],
            'is_dismissible' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
