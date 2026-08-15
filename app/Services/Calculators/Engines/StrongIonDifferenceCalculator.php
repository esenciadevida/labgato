<?php

namespace App\Services\Calculators\Engines;

class StrongIonDifferenceCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'sodium', 'potassium', 'chloride');

        $calcium = isset($inputs['ionised_calcium']) && $inputs['ionised_calcium'] !== ''
            ? $this->numericValues($inputs, 'ionised_calcium')['ionised_calcium']
            : 0.0;

        $magnesium = isset($inputs['magnesium']) && $inputs['magnesium'] !== ''
            ? $this->numericValues($inputs, 'magnesium')['magnesium']
            : 0.0;

        $lactate = isset($inputs['lactate']) && $inputs['lactate'] !== ''
            ? $this->numericValues($inputs, 'lactate')['lactate']
            : 0.0;

        $cations = $values['sodium'] + $values['potassium'] + $calcium + $magnesium;
        $anions = $values['chloride'] + $lactate;

        return [
            'sid' => $cations - $anions,
        ];
    }
}
