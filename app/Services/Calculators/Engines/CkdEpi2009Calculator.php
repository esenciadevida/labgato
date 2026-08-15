<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class CkdEpi2009Calculator extends AbstractFormulaCalculator
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
        $alpha = $sex === 'female' ? -0.329 : -0.411;

        $ratio = $scr / $kappa;

        $egfr = 141 * (min($ratio, 1) ** $alpha) * (max($ratio, 1) ** -1.209) * (0.993 ** $values['age']);

        if ($sex === 'female') {
            $egfr *= 1.018;
        }

        if (($inputs['race_black'] ?? 'no') === 'yes') {
            $egfr *= 1.159;
        }

        return [
            'egfr' => $egfr,
        ];
    }
}
