<?php

namespace App\Services\Calculators\Engines;

class ReticulocyteProductionIndexCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'reticulocyte_percent', 'patient_hematocrit', 'normal_hematocrit');

        $maturationFactor = match (true) {
            $values['patient_hematocrit'] >= 45 => 1.0,
            $values['patient_hematocrit'] >= 35 => 1.5,
            $values['patient_hematocrit'] >= 25 => 2.0,
            default => 2.5,
        };

        return [
            'rpi' => ($values['reticulocyte_percent'] * ($values['patient_hematocrit'] / $values['normal_hematocrit'])) / $maturationFactor,
        ];
    }
}
