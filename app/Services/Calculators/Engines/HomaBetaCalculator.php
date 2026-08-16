<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class HomaBetaCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'fasting_glucose', 'fasting_insulin');

        if ($values['fasting_glucose'] <= 63) {
            throw new InvalidArgumentException('Fasting glucose must be greater than 63 mg/dL for the HOMA-beta calculation.');
        }

        return [
            'homa_beta' => (360 * $values['fasting_insulin']) / ($values['fasting_glucose'] - 63),
        ];
    }
}
