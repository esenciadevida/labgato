<?php

namespace App\Services\Calculators\Engines;

class RbcIndicesCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'hematocrit', 'hemoglobin', 'rbc');

        return [
            'mcv' => ($values['hematocrit'] * 10) / $values['rbc'],
            'mch' => ($values['hemoglobin'] * 10) / $values['rbc'],
            'mchc' => ($values['hemoglobin'] * 100) / $values['hematocrit'],
        ];
    }
}
