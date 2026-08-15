<?php

namespace App\Services\Calculators\Engines;

class CorrectedReticulocyteCountCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'reticulocyte_percent', 'hematocrit');

        return [
            'corrected_reticulocyte_count' => $values['reticulocyte_percent'] * $values['hematocrit'] / 45,
        ];
    }
}
