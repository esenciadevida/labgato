<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidResourceSchema implements ValidationRule
{
    public function __construct(
        private readonly string $type = 'input',
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a JSON string.');

            return;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $fail('The :attribute must be valid JSON.');

            return;
        }

        if (! is_array($decoded) || $decoded === []) {
            $fail('The :attribute must contain at least one field.');

            return;
        }

        foreach ($decoded as $index => $field) {
            if (! is_array($field)) {
                $fail("The :attribute field #{$index} must be an object.");

                return;
            }

            if (! isset($field['key']) || ! is_string($field['key'])) {
                $fail("The :attribute field #{$index} must have a string key.");

                return;
            }

            if (! isset($field['label']) || ! is_string($field['label'])) {
                $fail("The :attribute field #{$index} must have a label.");

                return;
            }

            if ($this->type === 'input') {
                $this->validateInputField($field, $index, $fail);
            } else {
                $this->validateOutputField($field, $index, $fail);
            }
        }
    }

    private function validateInputField(array $field, int $index, Closure $fail): void
    {
        if (! preg_match('/^[a-z][a-z0-9_]*$/', $field['key'])) {
            $fail("The :attribute field #{$index} key must be snake_case.");
        }

        $type = $field['type'] ?? null;

        if (! in_array($type, ['number', 'select', 'text'], true)) {
            $fail("The :attribute field #{$index} type must be number, select, or text.");
        }

        if ($type === 'number') {
            if (isset($field['min']) && ! is_numeric($field['min'])) {
                $fail("The :attribute field #{$index} min must be numeric.");
            }

            if (isset($field['max']) && ! is_numeric($field['max'])) {
                $fail("The :attribute field #{$index} max must be numeric.");
            }
        }

        if ($type === 'select') {
            if (! isset($field['options']) || ! is_array($field['options']) || $field['options'] === []) {
                $fail("The :attribute field #{$index} must define options for select type.");
            }
        }
    }

    private function validateOutputField(array $field, int $index, Closure $fail): void
    {
        if (isset($field['rounding']) && ! in_array($field['rounding'], ['none', 'round_up', 'round_down', 'round_half_up'], true)) {
            $fail("The :attribute field #{$index} rounding is invalid.");
        }

        if (isset($field['precision']) && (! is_int($field['precision']) || $field['precision'] < 0 || $field['precision'] > 10)) {
            $fail("The :attribute field #{$index} precision must be an integer between 0 and 10.");
        }
    }
}
