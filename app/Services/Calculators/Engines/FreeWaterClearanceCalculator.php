<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class FreeWaterClearanceCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'urine_osmolality', 'serum_osmolality', 'urine_volume');

        if ($values['serum_osmolality'] == 0.0) {
            throw new InvalidArgumentException('Serum osmolality cannot be zero.');
        }

        return [
            'free_water_clearance' => $values['urine_volume'] * (1 - $values['urine_osmolality'] / $values['serum_osmolality']),
        ];
    }
}
