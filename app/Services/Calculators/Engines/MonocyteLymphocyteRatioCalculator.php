<?php

namespace App\Services\Calculators\Engines;

class MonocyteLymphocyteRatioCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'monocytes', 'lymphocytes');

        return [
            'mlr' => $values['monocytes'] / $values['lymphocytes'],
        ];
    }
}
