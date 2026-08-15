<?php

namespace App\Services\Calculators\Engines;

class CorrectedWbcCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'reported_wbc', 'nrbc_per_100_wbc');

        return [
            'corrected_wbc' => $values['reported_wbc'] * 100 / (100 + $values['nrbc_per_100_wbc']),
        ];
    }
}
