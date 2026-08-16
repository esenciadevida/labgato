<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class AntibioticDilutionCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'stock_concentration', 'desired_concentration', 'desired_volume');

        if ($values['stock_concentration'] <= 0 || $values['desired_concentration'] <= 0 || $values['desired_volume'] <= 0) {
            throw new InvalidArgumentException('All concentrations and volumes must be greater than zero.');
        }

        if ($values['desired_concentration'] > $values['stock_concentration']) {
            throw new InvalidArgumentException('Desired concentration cannot exceed the stock concentration.');
        }

        $stockVolume = ($values['desired_concentration'] * $values['desired_volume']) / $values['stock_concentration'];

        return [
            'stock_volume' => $stockVolume,
            'solvent_volume' => $values['desired_volume'] - $stockVolume,
        ];
    }
}
