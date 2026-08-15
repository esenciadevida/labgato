<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class AlbuminGlobulinRatioCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'albumin', 'total_protein');

        $globulin = $values['total_protein'] - $values['albumin'];

        if ($globulin == 0.0) {
            throw new InvalidArgumentException('Globulin (total protein minus albumin) cannot be zero.');
        }

        return [
            'ag_ratio' => $values['albumin'] / $globulin,
        ];
    }
}
