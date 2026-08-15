<?php

namespace App\Http\Requests\Admin;

use App\Models\Popup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePopupRequest extends FormRequest
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
            'trigger_type' => ['required', Rule::in([Popup::TRIGGER_ON_LOAD, Popup::TRIGGER_AFTER_DELAY])],
            'delay_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'is_active' => ['nullable', 'boolean'],
            'is_dismissible' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
