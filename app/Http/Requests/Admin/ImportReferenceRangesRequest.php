<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportReferenceRangesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:2048', 'mimetypes:'.implode(',', $this->allowedMimetypes())],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimetypes' => 'The :attribute must be a CSV or plain text file.',
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

    public function attributes(): array
    {
        return [
            'file' => 'CSV file',
        ];
    }
}