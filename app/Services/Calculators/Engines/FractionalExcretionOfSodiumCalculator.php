<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class FractionalExcretionOfSodiumCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'urine_sodium', 'serum_sodium', 'serum_creatinine', 'urine_creatinine');

        $denominator = $values['serum_sodium'] * $values['urine_creatinine'];

        if ($denominator == 0.0) {
            throw new InvalidArgumentException('Serum sodium and urine creatinine cannot be zero.');
        }

        return [
            'fena' => ($values['urine_sodium'] * $values['serum_creatinine']) / $denominator * 100,
        ];
    }
}
