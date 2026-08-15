<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class CorrectedTotalProteinCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'total_protein', 'fibrinogen');

        if ($values['fibrinogen'] < 0) {
            throw new InvalidArgumentException('Fibrinogen cannot be negative.');
        }

        if ($values['fibrinogen'] > $values['total_protein']) {
            throw new InvalidArgumentException('Fibrinogen cannot exceed total protein.');
        }

        return [
            'corrected_total_protein' => $values['total_protein'] - $values['fibrinogen'],
        ];
    }
}
