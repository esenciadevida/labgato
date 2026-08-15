<?php

namespace App\Services\Tools\Engines;

use App\Contracts\ToolInterface;
use InvalidArgumentException;

class NormalityConverter implements ToolInterface
{
    private const UNITS = [
        'n' => 1.0,
        'eq/l' => 1.0,
        'meq/l' => 1e-3,
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

        if (! isset(self::UNITS[$fromUnit])) {
            throw new InvalidArgumentException("Unsupported unit: {$fromUnit}.");
        }

        if (! isset(self::UNITS[$toUnit])) {
            throw new InvalidArgumentException("Unsupported unit: {$toUnit}.");
        }

        $result = $value * self::UNITS[$fromUnit] / self::UNITS[$toUnit];

        return [
            'result' => $result,
        ];
    }

    private static function normalizeUnit(string $unit): string
    {
        return mb_strtolower(str_replace(['μ', 'Μ'], 'µ', $unit));
    }
}
