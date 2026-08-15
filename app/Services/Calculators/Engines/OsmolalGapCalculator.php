<?php

namespace App\Services\Calculators\Engines;

class OsmolalGapCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'measured_osmolality', 'sodium', 'glucose', 'bun');

        $estimated = 2 * $values['sodium'] + $values['glucose'] / 18 + $values['bun'] / 2.8;

        return [
            'osmolal_gap' => $values['measured_osmolality'] - $estimated,
            'estimated_osmolality' => $estimated,
        ];
    }
}
