<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class MicDilutionCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'stock_concentration', 'dilution_factor', 'number_of_wells');

        if ($values['stock_concentration'] <= 0 || $values['dilution_factor'] <= 0) {
            throw new InvalidArgumentException('Stock concentration and dilution factor must be greater than zero.');
        }

        $wells = (int) $values['number_of_wells'];

        if ($wells < 1 || $wells > 8) {
            throw new InvalidArgumentException('Number of wells must be between 1 and 8.');
        }

        $result = [];

        for ($well = 1; $well <= $wells; $well++) {
            $result["well_{$well}"] = $values['stock_concentration'] / ($values['dilution_factor'] ** $well);
        }

        return $result;
    }
}
