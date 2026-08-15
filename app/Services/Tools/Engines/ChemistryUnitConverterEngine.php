<?php

namespace App\Services\Tools\Engines;

use App\Contracts\ToolInterface;
use InvalidArgumentException;

class ChemistryUnitConverterEngine implements ToolInterface
{
    private const MOLAR_UNITS = [
        'mol/l (m)' => 1.0,
        'mmol/l' => 1e-3,
        'µmol/l' => 1e-6,
        'nmol/l' => 1e-9,
    ];

    private const MASS_UNITS = [
        'g/l' => 1.0,
        'mg/l' => 1e-3,
        'µg/l' => 1e-6,
        'ng/ml' => 1e-6,
        'mg/dl' => 1e-2,
        'µg/dl' => 1e-5,
    ];

    private const REQUIRED_INPUTS = ['value', 'from_unit', 'to_unit'];

    public function calculate(array $inputs): array
    {
        foreach (self::REQUIRED_INPUTS as $key) {
            if (! array_key_exists($key, $inputs)) {
                throw new InvalidArgumentException("Missing required input: {$key}.");
            }
        }

        $value = (float) $inputs['value'];
        $fromUnit = self::normalizeUnit((string) $inputs['from_unit']);
        $toUnit = self::normalizeUnit((string) $inputs['to_unit']);

        $from = self::resolveUnit($fromUnit);
        $to = self::resolveUnit($toUnit);

        if ($from['dimension'] !== $to['dimension']) {
            throw new InvalidArgumentException('Cannot convert between molar and mass concentration units without the molar mass.');
        }

        $result = $value * $from['factor'] / $to['factor'];

        return [
            'result' => $result,
        ];
    }

    private static function resolveUnit(string $unit): array
    {
        if (isset(self::MOLAR_UNITS[$unit])) {
            return ['dimension' => 'molar', 'factor' => self::MOLAR_UNITS[$unit]];
        }

        if (isset(self::MASS_UNITS[$unit])) {
            return ['dimension' => 'mass', 'factor' => self::MASS_UNITS[$unit]];
        }

        throw new InvalidArgumentException("Unsupported unit: {$unit}.");
    }

    private static function normalizeUnit(string $unit): string
    {
        return mb_strtolower(str_replace(['μ', 'Μ'], 'µ', $unit));
    }
}
