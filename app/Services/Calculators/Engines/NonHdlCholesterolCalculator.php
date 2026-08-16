<?php

namespace App\Services\Calculators\Engines;

class NonHdlCholesterolCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'total_cholesterol', 'hdl');

        return [
            'non_hdl' => $values['total_cholesterol'] - $values['hdl'],
        ];
    }
}
