<?php

namespace App\Services\Calculators\Engines;

class CorrectedSodiumCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'sodium', 'glucose');

        return [
            'corrected_sodium' => $values['sodium'] + 1.6 * ($values['glucose'] - 100) / 100,
        ];
    }
}
