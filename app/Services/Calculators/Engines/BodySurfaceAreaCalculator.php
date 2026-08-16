<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class BodySurfaceAreaCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'weight', 'height');

        if ($values['weight'] <= 0 || $values['height'] <= 0) {
            throw new InvalidArgumentException('Weight and height must be greater than zero.');
        }

        return [
            'bsa_mosteller' => sqrt(($values['height'] * $values['weight']) / 3600),
        ];
    }
}
