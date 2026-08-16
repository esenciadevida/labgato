<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class McFarlandTurbidityCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'mcfarland');

        if ($values['mcfarland'] <= 0) {
            throw new InvalidArgumentException('The McFarland standard must be greater than zero.');
        }

        return [
            'cfu_per_ml' => $values['mcfarland'] * 3.0e8,
        ];
    }
}
