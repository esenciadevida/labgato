<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class MeldThreeCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'bilirubin', 'creatinine', 'inr', 'sodium', 'albumin');

        $sex = $inputs['sex'] ?? 'female';
        $dialysis = ($inputs['dialysis'] ?? 'no') === 'yes';

        if (! in_array($sex, ['male', 'female'], true)) {
            throw new InvalidArgumentException('Invalid sex selection.');
        }

        $isFemale = $sex === 'female';

        $bili = max($values['bilirubin'], 1.0);
        $inr = max($values['inr'], 1.0);

        $cr = max($values['creatinine'], 1.0);

        if ($dialysis) {
            $cr = 3.0;
        } else {
            $cr = min($cr, 3.0);
        }

        $na = max(125.0, min(137.0, $values['sodium']));
        $alb = max(1.5, min(3.5, $values['albumin']));

        $score = 1.33 * ($isFemale ? 1 : 0)
            + 4.56 * log($bili, M_E)
            + 0.82 * (137 - $na)
            - 0.24 * (137 - $na) * log($bili, M_E)
            + 9.09 * log($inr, M_E)
            + 11.14 * log($cr, M_E)
            + 1.85 * (3.5 - $alb)
            - 1.83 * (3.5 - $alb) * log($cr, M_E)
            + 6;

        $score = (float) round($score);
        $score = max(6.0, min(40.0, $score));

        return [
            'meld_3' => $score,
        ];
    }
}
