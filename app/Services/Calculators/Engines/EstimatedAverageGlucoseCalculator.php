<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class EstimatedAverageGlucoseCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'hba1c');

        if ($values['hba1c'] <= 0) {
            throw new InvalidArgumentException('HbA1c must be greater than zero.');
        }

        $eagMgdl = (28.7 * $values['hba1c']) - 46.7;

        return [
            'eag_mgdl' => $eagMgdl,
            'eag_mmol' => $eagMgdl / 18.016,
        ];
    }
}
