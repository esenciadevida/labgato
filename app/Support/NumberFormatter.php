<?php

namespace App\Support;

use InvalidArgumentException;

class NumberFormatter
{
    /**
     * @param  array{rounding?: string|null, precision?: int|null}  $rules
     */
    public function format(float|int|string $value, array $rules = []): string
    {
        $rounding = $rules['rounding'] ?? 'none';
        $precision = (int) ($rules['precision'] ?? 0);

        if (! in_array($rounding, ['none', 'round_up', 'round_down', 'round_half_up'])) {
            throw new InvalidArgumentException("Unsupported rounding mode: {$rounding}.");
        }

        if ($precision < 0 || $precision > 10) {
            throw new InvalidArgumentException('Precision must be between 0 and 10.');
        }

        $number = (float) $value;

        $result = match ($rounding) {
            'round_up' => $this->roundUp($number, $precision),
            'round_down' => $this->roundDown($number, $precision),
            'round_half_up' => round($number, $precision, PHP_ROUND_HALF_UP),
            default => $number,
        };

        return number_format($result, $precision, '.', '');
    }

    private function roundUp(float $value, int $precision): float
    {
        $factor = 10 ** $precision;

        return ceil($value * $factor) / $factor;
    }

    private function roundDown(float $value, int $precision): float
    {
        $factor = 10 ** $precision;

        return floor($value * $factor) / $factor;
    }
}
