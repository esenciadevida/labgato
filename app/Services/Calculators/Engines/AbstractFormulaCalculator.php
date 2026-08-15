<?php

namespace App\Services\Calculators\Engines;

use App\Contracts\CalculatorInterface;
use InvalidArgumentException;

abstract class AbstractFormulaCalculator implements CalculatorInterface
{
    /**
     * @param  array<string, mixed>  $inputs
     * @return array<string, float>
     */
    protected function numericValues(array $inputs, string ...$required): array
    {
        $values = [];

        foreach ($required as $key) {
            if (! array_key_exists($key, $inputs)) {
                throw new InvalidArgumentException("Missing required input: {$key}.");
            }

            if (! is_numeric($inputs[$key])) {
                throw new InvalidArgumentException("Input '{$key}' must be numeric.");
            }

            $values[$key] = (float) $inputs[$key];
        }

        return $values;
    }
}
