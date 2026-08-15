<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class DeltaRatioCalculator extends AbstractFormulaCalculator
{
    private const NORMAL_ANION_GAP = 12.0;

    private const NORMAL_BICARBONATE = 24.0;

    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'anion_gap', 'bicarbonate');

        $denominator = self::NORMAL_BICARBONATE - $values['bicarbonate'];

        if ($denominator == 0.0) {
            throw new InvalidArgumentException('A bicarbonate of 24 mEq/L makes the delta ratio undefined.');
        }

        return [
            'delta_ratio' => ($values['anion_gap'] - self::NORMAL_ANION_GAP) / $denominator,
        ];
    }
}
