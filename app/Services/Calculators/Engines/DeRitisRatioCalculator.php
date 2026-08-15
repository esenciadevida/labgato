<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class DeRitisRatioCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'ast', 'alt');

        if ($values['alt'] == 0.0) {
            throw new InvalidArgumentException('ALT cannot be zero.');
        }

        return [
            'ast_alt_ratio' => $values['ast'] / $values['alt'],
        ];
    }
}
