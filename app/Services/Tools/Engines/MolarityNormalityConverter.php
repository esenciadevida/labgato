<?php

namespace App\Services\Tools\Engines;

use App\Contracts\ToolInterface;
use InvalidArgumentException;

class MolarityNormalityConverter implements ToolInterface
{
    private const UNITS = [
        'mol/l (m)' => ['dimension' => 'molar', 'factor' => 1.0],
        'mmol/l' => ['dimension' => 'molar', 'factor' => 1e-3],
        'n' => ['dimension' => 'normality', 'factor' => 1.0],
        'meq/l' => ['dimension' => 'normality', 'factor' => 1e-3],
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

        $from = self::UNITS[$fromUnit];
        $to = self::UNITS[$toUnit];

        if ($from['dimension'] === $to['dimension']) {
            $result = $value * $from['factor'] / $to['factor'];
        } else {
            $nFactor = $inputs['n_factor'] ?? null;

            if (! is_numeric($nFactor) || (float) $nFactor <= 0) {
                throw new InvalidArgumentException('n-factor must be a number greater than zero.');
            }

            $nFactor = (float) $nFactor;
            $base = $value * $from['factor'];

            if ($from['dimension'] === 'molar') {
                $result = $base * $nFactor / $to['factor'];
            } else {
                $result = $base / $nFactor / $to['factor'];
            }
        }

        return [
            'result' => $result,
        ];
    }

    private static function normalizeUnit(string $unit): string
    {
        return mb_strtolower(str_replace(['μ', 'Μ'], 'µ', $unit));
    }
}
