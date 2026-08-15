<?php

namespace App\Services\Calculators\Engines;

class CorrectedCalciumCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'calcium', 'albumin');

        return [
            'corrected_calcium' => $values['calcium'] + 0.8 * (4 - $values['albumin']),
        ];
    }
}
