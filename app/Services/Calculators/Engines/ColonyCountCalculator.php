<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class ColonyCountCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'cfu_per_ml', 'dilution_factor', 'volume_plated');

        if ($values['dilution_factor'] <= 0 || $values['volume_plated'] <= 0) {
            throw new InvalidArgumentException('Dilution factor and volume plated must be greater than zero.');
        }

        return [
            'colonies' => ($values['cfu_per_ml'] * $values['volume_plated']) / $values['dilution_factor'],
        ];
    }
}
