<?php

namespace App\Services\Tools;

use App\Contracts\ToolInterface;
use App\Models\LaboratoryTool;
use App\Services\Calculators\Engines\GenericCalculatorEngine;
use App\Services\Tools\Engines\BufferPreparationCalculator;
use App\Services\Tools\Engines\ChemistryUnitConverterEngine;
use App\Services\Tools\Engines\DilutionCalculator;
use App\Services\Tools\Engines\MolarityConverter;
use App\Services\Tools\Engines\MolarityNormalityConverter;
use App\Services\Tools\Engines\NormalityConverter;
use App\Services\Tools\Engines\PercentageSolutionCalculator;
use App\Services\Tools\Engines\RpmToRcfConverter;
use App\Services\Tools\Engines\StockSolutionCalculator;
use App\Support\NumberFormatter;

class ToolService
{
    private const ALLOWED_ENGINES = [
        DilutionCalculator::class => 'Dilution Calculator',
        RpmToRcfConverter::class => 'RPM to RCF Converter',
        ChemistryUnitConverterEngine::class => 'Chemistry Unit Converter',
        PercentageSolutionCalculator::class => 'Percentage Solution Calculator',
        StockSolutionCalculator::class => 'Stock Solution Calculator',
        BufferPreparationCalculator::class => 'Buffer Preparation Calculator',
        MolarityConverter::class => 'Molarity Converter',
        NormalityConverter::class => 'Normality Converter',
        MolarityNormalityConverter::class => 'Molarity ↔ Normality Converter',
        GenericCalculatorEngine::class => 'Generic Calculator',
    ];

    private const ENGINE_TYPE = 'php_class';

    private string $fallbackEngine = GenericCalculatorEngine::class;

    public function availableEngines(): array
    {
        return self::ALLOWED_ENGINES;
    }

    public function engineLabel(string $engineReference): string
    {
        return self::ALLOWED_ENGINES[$engineReference] ?? $engineReference;
    }

    public function resolveEngine(LaboratoryTool $tool): ToolInterface
    {
        if ($tool->engine !== self::ENGINE_TYPE) {
            return app($this->fallbackEngine);
        }

        if (! isset(self::ALLOWED_ENGINES[$tool->engine_reference])) {
            return app($this->fallbackEngine);
        }

        $engineClass = $tool->engine_reference;

        $engine = app($engineClass);

        if (! $engine instanceof ToolInterface) {
            return app($this->fallbackEngine);
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

    public function run(LaboratoryTool $tool, array $inputs): array
    {
        $inputSchema = is_array($tool->input_schema) ? $tool->input_schema : [];
        $outputSchema = is_array($tool->output_schema) ? $tool->output_schema : [];

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
            $engine = $this->resolveEngine($tool);
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
