<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class BilirubinRatioCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'total_bilirubin', 'direct_bilirubin');

        if ($values['total_bilirubin'] == 0.0) {
            throw new InvalidArgumentException('Total bilirubin cannot be zero.');
        }

        if ($values['direct_bilirubin'] > $values['total_bilirubin']) {
            throw new InvalidArgumentException('Direct bilirubin cannot exceed total bilirubin.');
        }

        $indirect = $values['total_bilirubin'] - $values['direct_bilirubin'];

        return [
            'indirect_bilirubin' => $indirect,
            'direct_total_ratio' => $values['direct_bilirubin'] / $values['total_bilirubin'] * 100,
        ];
    }
}
