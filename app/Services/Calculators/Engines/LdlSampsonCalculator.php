<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class LdlSampsonCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'total_cholesterol', 'hdl', 'triglycerides');

        if ($values['triglycerides'] > 800) {
            throw new InvalidArgumentException('The Sampson method is not reliable when triglycerides exceed 800 mg/dL.');
        }

        $totalCholesterol = $values['total_cholesterol'];
        $nonHdl = $totalCholesterol - $values['hdl'];
        $triglycerides = $values['triglycerides'];

        $ldl = ($totalCholesterol / 0.948) - ($values['hdl'] / 0.971)
            - (($triglycerides / 8.56) + (($triglycerides * $nonHdl) / 2140) - ($triglycerides ** 2 / 16100)) / 1000;

        return [
            'ldl' => $ldl,
        ];
    }
}
