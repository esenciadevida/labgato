<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('questions.import') ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:5120', 'mimetypes:'.implode(',', $this->allowedMimetypes())],
            'import_as' => ['nullable', Rule::in(['draft', 'active'])],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimetypes' => 'The :attribute must be a CSV or plain text file.',
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'CSV file',
        ];
    }

    private function allowedMimetypes(): array
    {
        return [
            'text/csv',
            'text/plain',
            'text/x-csv',
            'text/comma-separated-values',
            'application/csv',
            'application/x-csv',
            'application/vnd.ms-excel',
        ];
    }
}
