<?php

namespace App\Services\Calculators\Engines;

class SystemicImmuneInflammationIndexCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'platelets', 'neutrophils', 'lymphocytes');

        return [
            'sii' => ($values['platelets'] * $values['neutrophils']) / $values['lymphocytes'],
        ];
    }
}
