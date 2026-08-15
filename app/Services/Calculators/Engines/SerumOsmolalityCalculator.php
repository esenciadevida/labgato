<?php

namespace App\Services\Calculators\Engines;

class SerumOsmolalityCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'sodium', 'glucose', 'bun');

        return [
            'estimated_osmolality' => 2 * $values['sodium'] + $values['glucose'] / 18 + $values['bun'] / 2.8,
        ];
    }
}
