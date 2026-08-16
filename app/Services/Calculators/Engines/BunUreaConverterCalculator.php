<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class BunUreaConverterCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'value');
        $value = $values['value'];
        $unit = (string) ($inputs['unit'] ?? '');

        if ($value <= 0) {
            throw new InvalidArgumentException('The value must be greater than zero.');
        }

        $bun = match ($unit) {
            'urea_mgdl' => $value / 2.1428,
            'urea_mmol' => $value / 0.357,
            default => $value,
        };

        return [
            'bun_mgdl' => $bun,
            'urea_mgdl' => $bun * 2.1428,
            'urea_mmol' => $bun * 0.357,
        ];
    }
}
