<?php

namespace App\Services;

use App\Models\ReferenceRange;

class ReferenceRangeService
{
    public function forParameter(string $key): ?ReferenceRange
    {
        return ReferenceRange::query()
            ->published()
            ->forParameterKey($key)
            ->orderByRaw("CASE WHEN sex = 'any' THEN 0 ELSE 1 END")
            ->orderBy('population_group')
            ->orderBy('name')
            ->first();
    }

    public function evaluate(float $value, string $calculatorUnit, ReferenceRange $range): ?string
    {
        if ($range->lower_limit === null && $range->upper_limit === null) {
            return null;
        }

        $multiplier = $this->unitMultiplier($calculatorUnit, $range);

        if ($multiplier === null) {
            return null;
        }

        $converted = $value * $multiplier;

        if ($range->lower_limit !== null && $converted < $range->lower_limit) {
            return 'low';
        }

        if ($range->upper_limit !== null && $converted > $range->upper_limit) {
            return 'high';
        }

        return 'normal';
    }

    private function unitMultiplier(string $calculatorUnit, ReferenceRange $range): ?float
    {
        $calculator = $this->normalizeUnit($calculatorUnit);

        if ($calculator === $this->normalizeUnit((string) $range->unit)) {
            return 1.0;
        }

        if ($range->si_unit !== null && $calculator === $this->normalizeUnit($range->si_unit)) {
            $factor = $range->conversion_factor;

            return $factor ? 1.0 / (float) $factor : null;
        }

        return null;
    }

    private function normalizeUnit(string $unit): string
    {
        $translations = [
            '⁰' => '0', '¹' => '1', '²' => '2', '³' => '3', '⁴' => '4',
            '⁵' => '5', '⁶' => '6', '⁷' => '7', '⁸' => '8', '⁹' => '9',
            'µ' => 'u', 'μ' => 'u', '×' => '', '^' => '', ' ' => '',
        ];

        return strtolower(strtr($unit, $translations));
    }
}
