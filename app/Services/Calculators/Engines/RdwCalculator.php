<?php

namespace App\Services\Calculators\Engines;

class RdwCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'rdw_sd', 'mcv');

        return [
            'rdw_cv' => ($values['rdw_sd'] / $values['mcv']) * 100,
        ];
    }
}
