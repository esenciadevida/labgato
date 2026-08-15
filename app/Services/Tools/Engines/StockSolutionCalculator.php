<?php

namespace App\Services\Tools\Engines;

use App\Contracts\ToolInterface;
use InvalidArgumentException;

class StockSolutionCalculator implements ToolInterface
{
    private const CONCENTRATION_UNITS = [
        'g/l' => ['dimension' => 'mass', 'factor' => 1.0],
        'mg/l' => ['dimension' => 'mass', 'factor' => 1e-3],
        'µg/l' => ['dimension' => 'mass', 'factor' => 1e-6],
        'ng/ml' => ['dimension' => 'mass', 'factor' => 1e-6],
        'mg/ml' => ['dimension' => 'mass', 'factor' => 1.0],
        'µg/ml' => ['dimension' => 'mass', 'factor' => 1e-3],
        'g/dl' => ['dimension' => 'mass', 'factor' => 10.0],
        'mol/l (m)' => ['dimension' => 'molar', 'factor' => 1.0],
        'mmol/l' => ['dimension' => 'molar', 'factor' => 1e-3],
        'µmol/l' => ['dimension' => 'molar', 'factor' => 1e-6],
        'nmol/l' => ['dimension' => 'molar', 'factor' => 1e-9],
    ];

    private const VOLUME_UNITS = [
        'l' => 1.0,
        'ml' => 1e-3,
        'µl' => 1e-6,
        'dl' => 1e-1,
    ];

    private const REQUIRED_INPUTS = ['concentration', 'concentration_unit', 'volume', 'volume_unit'];

    public function calculate(array $inputs): array
    {
        foreach (self::REQUIRED_INPUTS as $key) {
            if (! array_key_exists($key, $inputs)) {
                throw new InvalidArgumentException("Missing required input: {$key}.");
            }

            if (in_array($key, ['concentration', 'volume'], true) && ! is_numeric($inputs[$key])) {
                throw new InvalidArgumentException("Input '{$key}' must be numeric.");
            }
        }

        $concentration = (float) $inputs['concentration'];
        $volume = (float) $inputs['volume'];

        if ($concentration <= 0) {
            throw new InvalidArgumentException('Concentration must be greater than zero.');
        }

        if ($volume <= 0) {
            throw new InvalidArgumentException('Volume must be greater than zero.');
        }

        $concentrationUnit = self::normalizeUnit((string) $inputs['concentration_unit']);
        $volumeUnit = self::normalizeUnit((string) $inputs['volume_unit']);

        if (! isset(self::CONCENTRATION_UNITS[$concentrationUnit])) {
            throw new InvalidArgumentException("Unsupported concentration unit: {$concentrationUnit}.");
        }

        if (! isset(self::VOLUME_UNITS[$volumeUnit])) {
            throw new InvalidArgumentException("Unsupported volume unit: {$volumeUnit}.");
        }

        $concentrationFactor = self::CONCENTRATION_UNITS[$concentrationUnit]['factor'];
        $dimension = self::CONCENTRATION_UNITS[$concentrationUnit]['dimension'];

        $amount = $concentration * $concentrationFactor * $volume * self::VOLUME_UNITS[$volumeUnit];

        $outputKey = $dimension === 'molar' ? 'amount_mol' : 'amount_g';

        return [
            $outputKey => $amount,
        ];
    }

    private static function normalizeUnit(string $unit): string
    {
        return mb_strtolower(str_replace(['μ', 'Μ'], 'µ', $unit));
    }
}
