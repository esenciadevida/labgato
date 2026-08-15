<?php

namespace App\Services\Calculators\Engines;

class GreenKingIndexCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'mcv', 'rdw', 'hemoglobin');

        return [
            'green_king_index' => ($values['mcv'] ** 2 * $values['rdw']) / ($values['hemoglobin'] * 100),
        ];
    }
}
