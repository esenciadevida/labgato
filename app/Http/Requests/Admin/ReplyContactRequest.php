<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReplyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('contact-messages.reply') ?? false;
    }

    public function rules(): array
    {
        return [
            'reply' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }
}
