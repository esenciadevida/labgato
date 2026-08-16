<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class BasalMetabolicRateCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'weight', 'height', 'age');
        $sex = (string) ($inputs['sex'] ?? '');

        if ($values['weight'] <= 0 || $values['height'] <= 0 || $values['age'] <= 0) {
            throw new InvalidArgumentException('Weight, height and age must be greater than zero.');
        }

        $bmr = (10 * $values['weight']) + (6.25 * $values['height']) - (5 * $values['age']);

        if ($sex === 'female') {
            $bmr -= 161;
        } else {
            $bmr += 5;
        }

        return [
            'bmr' => $bmr,
        ];
    }
}
