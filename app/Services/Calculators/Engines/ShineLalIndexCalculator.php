<?php

namespace App\Services\Calculators\Engines;

class ShineLalIndexCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'mcv', 'mch');

        return [
            'shine_lal_index' => $values['mcv'] ** 2 * $values['mch'] * 0.01,
        ];
    }
}
