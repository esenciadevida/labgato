<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class Fib4Calculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'age', 'ast', 'alt', 'platelet_count');

        if ($values['platelet_count'] == 0.0) {
            throw new InvalidArgumentException('Platelet count cannot be zero.');
        }

        if ($values['alt'] < 0) {
            throw new InvalidArgumentException('ALT cannot be negative.');
        }

        return [
            'fib4' => ($values['age'] * $values['ast']) / ($values['platelet_count'] * sqrt($values['alt'])),
        ];
    }
}
