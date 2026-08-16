<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class BacterialConcentrationCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'od600', 'conversion_factor');

        if ($values['od600'] < 0 || $values['conversion_factor'] <= 0) {
            throw new InvalidArgumentException('OD600 must be non-negative and the conversion factor must be greater than zero.');
        }

        return [
            'cfu_per_ml' => $values['od600'] * $values['conversion_factor'],
        ];
    }
}
