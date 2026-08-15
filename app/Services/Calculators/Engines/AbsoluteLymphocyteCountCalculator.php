<?php

namespace App\Services\Calculators\Engines;

class AbsoluteLymphocyteCountCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'wbc', 'lymphocytes');

        return [
            'alc' => $values['wbc'] * $values['lymphocytes'] / 100,
        ];
    }
}
