<?php

namespace App\Services\Calculators\Engines;

class MentzerIndexCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'mcv', 'rbc');

        return [
            'mentzer_index' => $values['mcv'] / $values['rbc'],
        ];
    }
}
