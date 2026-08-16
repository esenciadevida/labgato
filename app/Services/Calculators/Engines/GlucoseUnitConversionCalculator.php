<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class GlucoseUnitConversionCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'value');
        $unit = (string) ($inputs['unit'] ?? '');

        if ($values['value'] <= 0) {
            throw new InvalidArgumentException('The glucose value must be greater than zero.');
        }

        $mgdl = $unit === 'mmol' ? $values['value'] * 18.0182 : $values['value'];

        return [
            'glucose_mgdl' => $mgdl,
            'glucose_mmol' => $mgdl / 18.0182,
        ];
    }
}
