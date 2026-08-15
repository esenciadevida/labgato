<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['publish', 'archive', 'delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $input = $this->input('ids', '');

        $ids = is_array($input)
            ? collect($input)
            : collect(explode(',', (string) $input));

        $ids = $ids
            ->map(fn ($id) => trim((string) $id))
            ->filter(fn ($id) => ctype_digit($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $this->merge([
            'ids' => $ids->all(),
        ]);
    }
}
