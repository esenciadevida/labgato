<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class OsmolarClearanceCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'urine_osmolality', 'serum_osmolality', 'urine_volume');

        if ($values['serum_osmolality'] == 0.0) {
            throw new InvalidArgumentException('Serum osmolality cannot be zero.');
        }

        return [
            'osmolar_clearance' => ($values['urine_osmolality'] * $values['urine_volume']) / $values['serum_osmolality'],
        ];
    }
}
