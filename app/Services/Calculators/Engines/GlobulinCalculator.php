<?php

namespace App\Services\Calculators\Engines;

class GlobulinCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'total_protein', 'albumin');

        return [
            'globulin' => $values['total_protein'] - $values['albumin'],
        ];
    }
}
