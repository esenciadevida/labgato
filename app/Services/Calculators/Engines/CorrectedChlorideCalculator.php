<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class CorrectedChlorideCalculator extends AbstractFormulaCalculator
{
    private const NORMAL_SODIUM = 140.0;

    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'chloride', 'sodium');

        if ($values['sodium'] == 0.0) {
            throw new InvalidArgumentException('Serum sodium cannot be zero.');
        }

        return [
            'corrected_chloride' => $values['chloride'] * self::NORMAL_SODIUM / $values['sodium'],
        ];
    }
}
