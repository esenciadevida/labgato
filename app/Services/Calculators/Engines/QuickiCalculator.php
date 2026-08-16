<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class QuickiCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'fasting_glucose', 'fasting_insulin');

        if ($values['fasting_glucose'] <= 0 || $values['fasting_insulin'] <= 0) {
            throw new InvalidArgumentException('Fasting glucose and insulin must be greater than zero.');
        }

        return [
            'quicki' => 1 / (log10($values['fasting_glucose']) + log10($values['fasting_insulin'])),
        ];
    }
}
