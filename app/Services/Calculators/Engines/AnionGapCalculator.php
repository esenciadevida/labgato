<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class AnionGapCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'sodium', 'chloride', 'bicarbonate');

        $potassiumMode = $inputs['potassium_mode'] ?? 'without';

        if (! in_array($potassiumMode, ['without', 'with'], true)) {
            throw new InvalidArgumentException('Invalid potassium mode.');
        }

        if ($potassiumMode === 'with') {
            $values['potassium'] = $this->numericValues($inputs, 'potassium')['potassium'];
        }

        $anionGap = $values['sodium'] - ($values['chloride'] + $values['bicarbonate']);

        if ($potassiumMode === 'with') {
            $anionGap += $values['potassium'];
        }

        $outputs = ['anion_gap' => $anionGap];

        if (($inputs['albumin_corrected'] ?? 'no') === 'yes') {
            $albumin = $this->numericValues($inputs, 'albumin')['albumin'];
            $outputs['corrected_anion_gap'] = $anionGap + 2.5 * (4 - $albumin);
        }

        return $outputs;
    }
}
