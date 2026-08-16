<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class TotalCholesterolHdlRatioCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'total_cholesterol', 'hdl');

        if ($values['hdl'] <= 0) {
            throw new InvalidArgumentException('HDL cholesterol must be greater than zero.');
        }

        return [
            'ratio' => $values['total_cholesterol'] / $values['hdl'],
        ];
    }
}
