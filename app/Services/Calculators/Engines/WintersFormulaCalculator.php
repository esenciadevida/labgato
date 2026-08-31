<?php

namespace App\Services\Calculators\Engines;

class WintersFormulaCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'bicarbonate');

        $anticipated_paco2 = 1.5 * $values['bicarbonate'] + 8;

        return [
            'anticipated_paco2' => $anticipated_paco2,
            'paco2_lower' => $anticipated_paco2 - 2,
            'paco2_upper' => $anticipated_paco2 + 2,
        ];
    }
}
