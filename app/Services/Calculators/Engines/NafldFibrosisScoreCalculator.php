<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class NafldFibrosisScoreCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'age', 'bmi', 'ast', 'alt', 'platelet_count', 'albumin');

        $ifg = $inputs['impaired_fasting_glucose'] ?? 'no';

        if (! in_array($ifg, ['no', 'yes'], true)) {
            throw new InvalidArgumentException('Invalid impaired fasting glucose selection.');
        }

        if ($values['alt'] == 0.0) {
            throw new InvalidArgumentException('ALT cannot be zero.');
        }

        $diabetes = $ifg === 'yes' ? 1.0 : 0.0;

        $score = -1.675
            + 0.037 * $values['age']
            + 0.094 * $values['bmi']
            + 1.13 * $diabetes
            + 0.99 * ($values['ast'] / $values['alt'])
            - 0.013 * $values['platelet_count']
            - 0.66 * $values['albumin'];

        return [
            'nafld_fibrosis_score' => $score,
        ];
    }
}
