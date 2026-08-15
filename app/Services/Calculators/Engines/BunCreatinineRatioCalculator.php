<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class BunCreatinineRatioCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'bun', 'creatinine');

        if ($values['creatinine'] == 0.0) {
            throw new InvalidArgumentException('Serum creatinine cannot be zero.');
        }

        return [
            'bun_creatinine_ratio' => $values['bun'] / $values['creatinine'],
        ];
    }
}
