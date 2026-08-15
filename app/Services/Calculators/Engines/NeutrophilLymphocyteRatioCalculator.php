<?php

namespace App\Services\Calculators\Engines;

class NeutrophilLymphocyteRatioCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'neutrophils', 'lymphocytes');

        return [
            'nlr' => $values['neutrophils'] / $values['lymphocytes'],
        ];
    }
}
