<?php

namespace App\Services\Calculators;

use App\Contracts\CalculatorInterface;
use App\Models\Calculator;
use App\Services\Calculators\Engines\AnionGapCalculator;
use App\Support\NumberFormatter;
use InvalidArgumentException;

class CalculatorService
{
    private const ALLOWED_ENGINES = [
        AnionGapCalculator::class => 'Anion Gap Calculator',
    ];

    private const ENGINE_TYPE = 'php_class';

    public function availableEngines(): array
    {
        return self::ALLOWED_ENGINES;
    }

    public function engineLabel(string $engineReference): string
    {
        return self::ALLOWED_ENGINES[$engineReference] ?? $engineReference;
    }

    public function resolveEngine(Calculator $calculator): CalculatorInterface
    {
        if ($calculator->engine !== self::ENGINE_TYPE) {
            throw new InvalidArgumentException('Unsupported engine type: '.$calculator->engine);
        }

        if (! isset(self::ALLOWED_ENGINES[$calculator->engine_reference])) {
            throw new InvalidArgumentException('Calculator engine is not registered.');
        }

        $engineClass = $calculator->engine_reference;

        $engine = app($engineClass);

        if (! $engine instanceof CalculatorInterface) {
            throw new InvalidArgumentException('Calculator engine does not implement CalculatorInterface.');
        }

        return $engine;
    }

    public function validateInputs(array $inputSchema, array $inputs): array
    {
        $errors = [];

        foreach ($inputSchema as $field) {
            $key = $field['key'] ?? null;
            $label = $field['label'] ?? $key;

            if ($key === null) {
                continue;
            }

            $value = $inputs[$key] ?? null;
            $isRequired = (bool) ($field['required'] ?? false);
            $isEmpty = $value === null || $value === '';

            if ($isRequired && $isEmpty) {
                $errors[$key][] = "{$label} is required.";

                continue;
            }

            if ($isEmpty) {
                continue;
            }

            $type = $field['type'] ?? 'text';

            if ($type === 'number' && ! is_numeric($value)) {
                $errors[$key][] = "{$label} must be a number.";

                continue;
            }

            if ($type === 'select' && isset($field['options']) && is_array($field['options'])) {
                $allowed = array_column($field['options'], 'value');
                if (! in_array((string) $value, $allowed)) {
                    $errors[$key][] = "{$label} contains an invalid selection.";
                }
            }

            if (is_numeric($value)) {
                $number = (float) $value;

                if (isset($field['min']) && is_numeric($field['min']) && $number < (float) $field['min']) {
                    $errors[$key][] = "{$label} must be at least {$field['min']}.";
                }

                if (isset($field['max']) && is_numeric($field['max']) && $number > (float) $field['max']) {
                    $errors[$key][] = "{$label} must be at most {$field['max']}.";
                }
            }
        }

        return $errors;
    }

    public function run(Calculator $calculator, array $inputs): array
    {
        $inputSchema = is_array($calculator->input_schema) ? $calculator->input_schema : [];
        $outputSchema = is_array($calculator->output_schema) ? $calculator->output_schema : [];

        $errors = $this->validateInputs($inputSchema, $inputs);

        if (! empty($errors)) {
            return ['outputs' => [], 'errors' => $errors];
        }

        $engineInputs = [];

        foreach ($inputSchema as $field) {
            $key = $field['key'] ?? null;

            if ($key === null) {
                continue;
            }

            if (isset($inputs[$key]) && $inputs[$key] !== '') {
                $engineInputs[$key] = $inputs[$key];
            }
        }

        try {
            $engine = $this->resolveEngine($calculator);
            $rawOutputs = $engine->calculate($engineInputs);
        } catch (\Throwable $e) {
            return ['outputs' => [], 'errors' => ['_calculation' => ['Unable to calculate a result.']]];
        }

        $outputs = [];

        foreach ($outputSchema as $field) {
            $key = $field['key'] ?? null;

            if ($key === null || ! array_key_exists($key, $rawOutputs)) {
                continue;
            }

            $formatter = app(NumberFormatter::class);
            $value = $formatter->format($rawOutputs[$key], [
                'rounding' => $field['rounding'] ?? 'none',
                'precision' => $field['precision'] ?? 0,
            ]);

            $outputs[$key] = [
                'label' => $field['label'] ?? $key,
                'unit' => $field['unit'] ?? null,
                'value' => $value,
            ];
        }

        return ['outputs' => $outputs, 'errors' => []];
    }
}
