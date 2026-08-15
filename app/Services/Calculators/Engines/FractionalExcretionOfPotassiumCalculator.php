<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class FractionalExcretionOfPotassiumCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'urine_potassium', 'serum_potassium', 'serum_creatinine', 'urine_creatinine');

        $denominator = $values['serum_potassium'] * $values['urine_creatinine'];

        if ($denominator == 0.0) {
            throw new InvalidArgumentException('Serum potassium and urine creatinine cannot be zero.');
        }

        return [
            'fek' => ($values['urine_potassium'] * $values['serum_creatinine']) / $denominator * 100,
        ];
    }
}
