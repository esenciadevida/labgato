<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class TriglycerideHdlRatioCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'triglycerides', 'hdl');

        if ($values['hdl'] <= 0) {
            throw new InvalidArgumentException('HDL cholesterol must be greater than zero.');
        }

        return [
            'ratio' => $values['triglycerides'] / $values['hdl'],
        ];
    }
}
