<?php

namespace App\Services\Calculators\Engines;

class UrineAnionGapCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'urine_sodium', 'urine_potassium', 'urine_chloride');

        return [
            'urine_anion_gap' => ($values['urine_sodium'] + $values['urine_potassium']) - $values['urine_chloride'],
        ];
    }
}
