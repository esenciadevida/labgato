<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class CfuPerMlCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'colonies', 'dilution_factor', 'volume_plated');

        if ($values['dilution_factor'] <= 0 || $values['volume_plated'] <= 0) {
            throw new InvalidArgumentException('Dilution factor and volume plated must be greater than zero.');
        }

        return [
            'cfu_per_ml' => ($values['colonies'] * $values['dilution_factor']) / $values['volume_plated'],
        ];
    }
}
