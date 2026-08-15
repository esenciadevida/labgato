<?php

namespace App\Services\Calculators\Engines;

class AbsoluteEosinophilCountCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'wbc', 'eosinophils');

        return [
            'aec' => $values['wbc'] * $values['eosinophils'] / 100,
        ];
    }
}
