<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidResourceSchema implements ValidationRule
{
    private const MAX_JSON_LENGTH = 65536;

    private const MAX_JSON_DEPTH = 8;

    private const MAX_FIELDS = 50;

    private const MAX_KEY_LENGTH = 64;

    private const MAX_LABEL_LENGTH = 255;

    private const KEY_PATTERN = '/^[a-z][a-z0-9_]*$/';

    public function __construct(
        private readonly string $type = 'input',
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a JSON string.');

            return;
        }

        if (strlen($value) > self::MAX_JSON_LENGTH) {
            $fail('The :attribute must not exceed 64 KB.');

            return;
        }

        $decoded = json_decode($value, true, self::MAX_JSON_DEPTH);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $fail('The :attribute must be valid JSON.');

            return;
        }

        if (! is_array($decoded) || $decoded === []) {
            $fail('The :attribute must contain at least one field.');

            return;
        }

        if (! array_is_list($decoded)) {
            $fail('The :attribute must be a JSON array of fields.');

            return;
        }

        if (count($decoded) > self::MAX_FIELDS) {
            $fail('The :attribute must not contain more than '.self::MAX_FIELDS.' fields.');

            return;
        }

        $seenKeys = [];

        foreach ($decoded as $index => $field) {
            if (! is_array($field)) {
                $fail("The :attribute field #{$index} must be an object.");

                return;
            }

            if (! isset($field['key']) || ! is_string($field['key'])) {
                $fail("The :attribute field #{$index} must have a string key.");

                return;
            }

            if (! preg_match(self::KEY_PATTERN, $field['key'])) {
                $fail("The :attribute field #{$index} key must be snake_case.");

                return;
            }

            if (strlen($field['key']) > self::MAX_KEY_LENGTH) {
                $fail("The :attribute field #{$index} key must not exceed ".self::MAX_KEY_LENGTH.' characters.');

                return;
            }

            if (isset($seenKeys[$field['key']])) {
                $fail("The :attribute field #{$index} key must be unique.");

                return;
            }

            $seenKeys[$field['key']] = true;

            if (! isset($field['label']) || ! is_string($field['label'])) {
                $fail("The :attribute field #{$index} must have a label.");

                return;
            }

            if (strlen($field['label']) > self::MAX_LABEL_LENGTH) {
                $fail("The :attribute field #{$index} label must not exceed ".self::MAX_LABEL_LENGTH.' characters.');

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

                return;
            }

            foreach ($field['options'] as $optionIndex => $option) {
                if (! is_array($option) || ! array_key_exists('value', $option)) {
                    $fail("The :attribute field #{$index} option #{$optionIndex} must define a value.");

                    return;
                }
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

        if (isset($field['reference_key']) && (! is_string($field['reference_key']) || ! preg_match(self::KEY_PATTERN, $field['reference_key']))) {
            $fail("The :attribute field #{$index} reference_key must be snake_case.");
        }
    }
}
