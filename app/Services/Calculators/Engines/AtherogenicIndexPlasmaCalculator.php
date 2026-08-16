<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class AtherogenicIndexPlasmaCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'triglycerides', 'hdl');

        if ($values['hdl'] <= 0 || $values['triglycerides'] <= 0) {
            throw new InvalidArgumentException('Triglycerides and HDL must be greater than zero.');
        }

        return [
            'aip' => log10($values['triglycerides'] / $values['hdl']),
        ];
    }
}
