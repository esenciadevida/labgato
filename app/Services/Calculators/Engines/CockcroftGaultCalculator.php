<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class CockcroftGaultCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'age', 'weight', 'serum_creatinine');

        $sex = $inputs['sex'] ?? 'male';

        if (! in_array($sex, ['male', 'female'], true)) {
            throw new InvalidArgumentException('Invalid sex selection.');
        }

        if ($values['weight'] <= 0 || $values['serum_creatinine'] <= 0) {
            throw new InvalidArgumentException('Weight and serum creatinine must be positive.');
        }

        $crcl = ((140 - $values['age']) * $values['weight']) / (72 * $values['serum_creatinine']);

        if ($sex === 'female') {
            $crcl *= 0.85;
        }

        return [
            'crcl' => $crcl,
        ];
    }
}
