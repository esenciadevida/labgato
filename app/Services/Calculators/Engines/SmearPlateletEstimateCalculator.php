<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class SmearPlateletEstimateCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'platelets_per_field');

        if ($values['platelets_per_field'] < 0) {
            throw new InvalidArgumentException('Platelets per field must be non-negative.');
        }

        return [
            'estimated_platelets' => $values['platelets_per_field'] * 20000,
        ];
    }
}
