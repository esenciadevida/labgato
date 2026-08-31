<?php

namespace App\Services\Calculators\Engines;

class CalciumPhosphateProductCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'calcium', 'phosphate');

        return [
            'calcium_phosphate_product' => $values['calcium'] * $values['phosphate'],
        ];
    }
}
