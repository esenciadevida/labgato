<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class RenalFailureIndexCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'urine_sodium', 'serum_creatinine', 'urine_creatinine');

        if ($values['urine_creatinine'] == 0.0) {
            throw new InvalidArgumentException('Urine creatinine cannot be zero.');
        }

        return [
            'rfi' => ($values['urine_sodium'] * $values['serum_creatinine']) / $values['urine_creatinine'],
        ];
    }
}
