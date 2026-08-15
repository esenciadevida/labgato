<?php

namespace App\Services\Calculators\Engines;

class SrivastavaIndexCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'mch', 'rbc');

        return [
            'srivastava_index' => $values['mch'] / $values['rbc'],
        ];
    }
}
