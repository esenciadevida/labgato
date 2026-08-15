<?php

namespace App\Services\Calculators\Engines;

class McvCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'hematocrit', 'rbc');

        return [
            'mcv' => ($values['hematocrit'] * 10) / $values['rbc'],
        ];
    }
}
