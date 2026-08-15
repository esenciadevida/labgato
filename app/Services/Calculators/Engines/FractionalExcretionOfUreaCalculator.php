<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class FractionalExcretionOfUreaCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'urine_urea', 'serum_urea', 'serum_creatinine', 'urine_creatinine');

        $denominator = $values['serum_urea'] * $values['urine_creatinine'];

        if ($denominator == 0.0) {
            throw new InvalidArgumentException('Serum urea and urine creatinine cannot be zero.');
        }

        return [
            'feurea' => ($values['urine_urea'] * $values['serum_creatinine']) / $denominator * 100,
        ];
    }
}
