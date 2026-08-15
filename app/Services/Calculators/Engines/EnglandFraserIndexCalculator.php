<?php

namespace App\Services\Calculators\Engines;

class EnglandFraserIndexCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'mcv', 'rbc', 'hemoglobin');

        return [
            'england_fraser_index' => $values['mcv'] - $values['rbc'] - (5 * $values['hemoglobin']) - 3.4,
        ];
    }
}
