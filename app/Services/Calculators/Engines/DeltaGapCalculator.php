<?php

namespace App\Services\Calculators\Engines;

class DeltaGapCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'anion_gap', 'normal_gap');

        return [
            'delta_gap' => $values['anion_gap'] - $values['normal_gap'],
        ];
    }
}
