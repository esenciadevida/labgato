<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class ApriCalculator extends AbstractFormulaCalculator
{
    private const AST_UPPER_LIMIT_NORMAL = 40.0;

    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'ast', 'platelet_count');

        if ($values['platelet_count'] == 0.0) {
            throw new InvalidArgumentException('Platelet count cannot be zero.');
        }

        return [
            'apri' => $values['ast'] / self::AST_UPPER_LIMIT_NORMAL / $values['platelet_count'] * 100,
        ];
    }
}
