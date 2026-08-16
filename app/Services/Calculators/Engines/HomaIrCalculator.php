<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class HomaIrCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'fasting_glucose', 'fasting_insulin');

        if ($values['fasting_glucose'] <= 0) {
            throw new InvalidArgumentException('Fasting glucose must be greater than zero.');
        }

        return [
            'homa_ir' => ($values['fasting_glucose'] * $values['fasting_insulin']) / 405,
        ];
    }
}
