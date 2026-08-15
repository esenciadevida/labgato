<?php

namespace App\Services\Calculators\Engines;

class BicarbonateGapCalculator extends AbstractFormulaCalculator
{
    private const NORMAL_BICARBONATE = 24.0;

    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'anion_gap', 'bicarbonate');

        return [
            'bicarbonate_gap' => $values['anion_gap'] - (self::NORMAL_BICARBONATE - $values['bicarbonate']),
        ];
    }
}
