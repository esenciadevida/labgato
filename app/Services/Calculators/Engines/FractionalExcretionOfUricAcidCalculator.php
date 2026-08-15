<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class FractionalExcretionOfUricAcidCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'urine_uric_acid', 'serum_uric_acid', 'serum_creatinine', 'urine_creatinine');

        $denominator = $values['serum_uric_acid'] * $values['urine_creatinine'];

        if ($denominator == 0.0) {
            throw new InvalidArgumentException('Serum uric acid and urine creatinine cannot be zero.');
        }

        return [
            'feua' => ($values['urine_uric_acid'] * $values['serum_creatinine']) / $denominator * 100,
        ];
    }
}
