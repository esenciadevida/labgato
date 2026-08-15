<?php

namespace App\Services\Calculators\Engines;

class AncCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'wbc', 'neutrophils');
        $bands = isset($inputs['bands']) && is_numeric($inputs['bands']) ? (float) $inputs['bands'] : 0.0;

        return [
            'anc' => $values['wbc'] * ($values['neutrophils'] + $bands) / 100,
        ];
    }
}
