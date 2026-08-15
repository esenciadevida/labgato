<?php

namespace App\Services\Calculators\Engines;

class MchCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'hemoglobin', 'rbc');

        return [
            'mch' => ($values['hemoglobin'] * 10) / $values['rbc'],
        ];
    }
}
