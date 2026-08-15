<?php

namespace App\Services\Calculators\Engines;

class RdwIndexCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'mcv', 'rdw', 'rbc');

        return [
            'rdw_index' => ($values['mcv'] * $values['rdw']) / $values['rbc'],
        ];
    }
}
