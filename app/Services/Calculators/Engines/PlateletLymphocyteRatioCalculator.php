<?php

namespace App\Services\Calculators\Engines;

class PlateletLymphocyteRatioCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'platelets', 'lymphocytes');

        return [
            'plr' => $values['platelets'] / $values['lymphocytes'],
        ];
    }
}
