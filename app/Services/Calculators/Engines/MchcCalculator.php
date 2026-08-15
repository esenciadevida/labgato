<?php

namespace App\Services\Calculators\Engines;

class MchcCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'hemoglobin', 'hematocrit');

        return [
            'mchc' => ($values['hemoglobin'] * 100) / $values['hematocrit'],
        ];
    }
}
