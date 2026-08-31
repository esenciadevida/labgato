<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class CorrectedPhenytoinCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'measured_phenytoin', 'albumin');

        if ($values['albumin'] < 0) {
            throw new InvalidArgumentException('Albumin must be non-negative.');
        }

        $denominator = 0.2 * $values['albumin'] + 0.1;

        if ($denominator <= 0) {
            throw new InvalidArgumentException('The correction denominator must be positive.');
        }

        return [
            'corrected_phenytoin' => $values['measured_phenytoin'] / $denominator,
        ];
    }
}
