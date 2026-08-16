<?php

namespace App\Services\Calculators\Engines;

use InvalidArgumentException;

class LdlMartinHopkinsCalculator extends AbstractFormulaCalculator
{
    public function calculate(array $inputs): array
    {
        $values = $this->numericValues($inputs, 'total_cholesterol', 'hdl', 'triglycerides');

        if ($values['triglycerides'] > 800) {
            throw new InvalidArgumentException('The Martin-Hopkins method is not reliable when triglycerides exceed 800 mg/dL.');
        }

        $nonHdl = $values['total_cholesterol'] - $values['hdl'];
        $factor = $this->adjustableFactor($values['triglycerides'], $nonHdl);

        return [
            'ldl' => $values['total_cholesterol'] - $values['hdl'] - ($values['triglycerides'] / $factor),
        ];
    }

    private function adjustableFactor(float $triglycerides, float $nonHdl): int
    {
        $rowBounds = [100, 150, 200, 250, 300, 350, 400, 450, 500];

        $rows = [
            [5, 5, 5, 5, 5, 5, 5, 5, 6, 6, 6, 7],
            [5, 5, 5, 5, 5, 5, 5, 6, 6, 7, 7, 8],
            [5, 5, 5, 5, 5, 5, 6, 6, 7, 7, 8, 9],
            [5, 5, 5, 5, 5, 6, 6, 7, 8, 8, 9, 9],
            [5, 5, 5, 5, 6, 6, 7, 8, 8, 9, 9, 10],
            [5, 5, 5, 5, 6, 7, 7, 8, 9, 9, 10, 10],
            [5, 5, 5, 6, 6, 7, 8, 8, 9, 10, 10, 11],
            [5, 5, 5, 6, 6, 7, 8, 9, 9, 10, 11, 11],
            [5, 5, 5, 6, 7, 7, 8, 9, 10, 10, 11, 12],
            [5, 5, 6, 6, 7, 8, 8, 9, 10, 11, 12, 12],
        ];

        $rowIndex = count($rows) - 1;

        foreach ($rowBounds as $index => $threshold) {
            if ($triglycerides < $threshold) {
                $rowIndex = $index;

                break;
            }
        }

        $columnBounds = [100, 130, 160, 190, 220, 250, 300, 350, 400, 450, 500];

        $columnIndex = count($rows[0]) - 1;

        foreach ($columnBounds as $index => $threshold) {
            if ($nonHdl < $threshold) {
                $columnIndex = $index;

                break;
            }
        }

        return $rows[$rowIndex][$columnIndex];
    }
}
