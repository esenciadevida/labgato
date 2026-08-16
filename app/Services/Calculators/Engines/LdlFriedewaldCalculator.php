<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class LdlFriedewaldCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'total_cholesterol', 'hdl', 'triglycerides');

        if ($values['triglycerides'] > 400) {
            throw new InvalidArgumentException('The Friedewald equation is not valid when triglycerides exceed 400 mg/dL.');
        }

        return [
            'ldl' => $values['total_cholesterol'] - $values['hdl'] - ($values['triglycerides'] / 5),
        ];
    }
}
