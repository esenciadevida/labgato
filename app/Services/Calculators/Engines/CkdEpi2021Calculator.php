<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class CkdEpi2021Calculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'serum_creatinine', 'age');

        $sex = $inputs['sex'] ?? 'male';

        if (! in_array($sex, ['male', 'female'], true)) {
            throw new InvalidArgumentException('Invalid sex selection.');
        }

        $scr = $values['serum_creatinine'];

        if ($scr <= 0 || $values['age'] <= 0) {
            throw new InvalidArgumentException('Serum creatinine and age must be positive.');
        }

        $kappa = $sex === 'female' ? 0.7 : 0.9;
        $alpha = $sex === 'female' ? -0.241 : -0.302;

        $ratio = $scr / $kappa;

        $egfr = 142 * (min($ratio, 1) ** $alpha) * (max($ratio, 1) ** -1.200) * (0.9938 ** $values['age']);

        if ($sex === 'female') {
            $egfr *= 1.012;
        }

        return [
            'egfr' => $egfr,
        ];
    }
}
