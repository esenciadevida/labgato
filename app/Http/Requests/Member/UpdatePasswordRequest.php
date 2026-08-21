<?php

namespace App\Http\Requests\Member;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isMember() ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'password' => [
                'required',
                'confirmed',
                Password::min(config('labgoto.security.password_min_length', 8))->letters()->numbers(),
            ],
        ];

        if (! $this->user()->hasCustomPassword()) {
            $rules['current_password'] = ['nullable', 'string'];

            return $rules;
        }

        $rules['current_password'] = [
            'required',
            'string',
            function (string $attribute, mixed $value, Closure $fail) {
                if (! Hash::check((string) $value, $this->user()->password)) {
                    $fail('Your current password is incorrect.');
                }
            },
        ];

        return $rules;
    }
}
