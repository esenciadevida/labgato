<?php

namespace App\Http\Requests\Admin;

use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('announcements.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:500', 'not_regex:/^\s*$/'],
            'type' => ['required', Rule::in(Announcement::TYPES)],
            'link_text' => ['nullable', 'string', 'max:100'],
            'link_url' => ['nullable', 'string', 'max:500', 'url'],
            'is_active' => ['nullable', 'boolean'],
            'is_dismissible' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
