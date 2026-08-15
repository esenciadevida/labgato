<?php

namespace App\Services\Calculators\Engines;

class ReticulocyteIndexCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'reticulocyte_percent', 'hematocrit');

        return [
            'reticulocyte_index' => $values['reticulocyte_percent'] * $values['hematocrit'] / 45,
        ];
    }
}
