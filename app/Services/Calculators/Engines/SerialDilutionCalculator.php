<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class SerialDilutionCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'initial_concentration', 'dilution_factor', 'number_of_tubes');

        if ($values['initial_concentration'] <= 0 || $values['dilution_factor'] <= 0) {
            throw new InvalidArgumentException('Initial concentration and dilution factor must be greater than zero.');
        }

        $tubes = (int) $values['number_of_tubes'];

        if ($tubes < 1 || $tubes > 6) {
            throw new InvalidArgumentException('Number of tubes must be between 1 and 6.');
        }

        $result = [];

        for ($tube = 1; $tube <= $tubes; $tube++) {
            $result["tube_{$tube}"] = $values['initial_concentration'] / ($values['dilution_factor'] ** $tube);
        }

        return $result;
    }
}
