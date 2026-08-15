<?php

namespace App\Services\Tools\Engines;

use App\Contracts\ToolInterface;
use InvalidArgumentException;

class DilutionCalculator implements ToolInterface
{
    private const REQUIRED_INPUTS = ['stock_concentration', 'final_concentration', 'final_volume'];

    public function calculate(array $inputs): array
    {
        foreach (self::REQUIRED_INPUTS as $key) {
            if (! array_key_exists($key, $inputs)) {
                throw new InvalidArgumentException("Missing required input: {$key}.");
            }

            if (! is_numeric($inputs[$key])) {
                throw new InvalidArgumentException("Input '{$key}' must be numeric.");
            }
        }

        $stockConcentration = (float) $inputs['stock_concentration'];
        $finalConcentration = (float) $inputs['final_concentration'];
        $finalVolume = (float) $inputs['final_volume'];

        if ($stockConcentration <= 0) {
            throw new InvalidArgumentException('Stock concentration must be greater than zero.');
        }

        $stockVolume = $finalConcentration * $finalVolume / $stockConcentration;

        return [
            'stock_volume' => $stockVolume,
            'diluent_volume' => $finalVolume - $stockVolume,
        ];
    }
}
