<?php

namespace App\Services\Calculators\Engines;

use App\Contracts\CalculatorInterface;
use InvalidArgumentException;

class AnionGapCalculator implements CalculatorInterface
{
    private const REQUIRED_INPUTS = ['sodium', 'chloride', 'bicarbonate'];

    public function calculate(array $inputs): array
    {
        foreach (self::REQUIRED_INPUTS as $key) {
            if (! array_key_exists($key, $inputs)) {
                throw new InvalidArgumentException("Missing required input: {$key}.");
            }

            if (! is_numeric($inputs[$key])) {
                throw new InvalidArgumentException("Input '{$key}' must be numeric.");
            }
        }

        $sodium = (float) $inputs['sodium'];
        $chloride = (float) $inputs['chloride'];
        $bicarbonate = (float) $inputs['bicarbonate'];

        return [
            'anion_gap' => $sodium - ($chloride + $bicarbonate),
        ];
    }
}
