<?php

namespace App\Http\Requests\Member;

use App\Models\UserProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isMember() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],

            'display_name' => ['nullable', 'string', 'max:60'],
            'country' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],

            'professional_status' => ['nullable', Rule::in(UserProfile::STATUSES)],

            // Student fields
            'institution' => ['nullable', 'string', 'max:255'],
            'programme' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:150'],
            'graduation_year' => ['nullable', 'integer', 'min:1980', 'max:'.(now()->year + 10)],

            // Professional fields
            'employer' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'specialization' => ['nullable', 'string', 'max:255'],

            // Privacy controls
            'show_country' => ['nullable', 'boolean'],
            'show_institution' => ['nullable', 'boolean'],
            'show_on_leaderboard' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.image' => 'The profile photo must be an image.',
            'photo.mimes' => 'The photo must be a JPG, PNG, or WebP file.',
            'photo.max' => 'The photo may not be larger than 2MB.',
        ];
    }
}
