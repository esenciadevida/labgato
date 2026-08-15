<?php

namespace App\Services\Tools\Engines;

use App\Contracts\ToolInterface;
use InvalidArgumentException;

class PercentageSolutionCalculator implements ToolInterface
{
    private const CONVENTIONS = ['w/v', 'v/v', 'w/w'];

    private const REQUIRED_INPUTS = ['convention', 'amount', 'amount_unit', 'total_quantity', 'total_unit'];

    private const UNITS = [
        'g' => ['dimension' => 'mass', 'factor' => 1.0],
        'mg' => ['dimension' => 'mass', 'factor' => 1e-3],
        'µg' => ['dimension' => 'mass', 'factor' => 1e-6],
        'ml' => ['dimension' => 'volume', 'factor' => 1.0],
        'µl' => ['dimension' => 'volume', 'factor' => 1e-3],
        'l' => ['dimension' => 'volume', 'factor' => 1e3],
    ];

    public function calculate(array $inputs): array
    {
        $convention = (string) ($inputs['convention'] ?? '');

        if (! in_array($convention, self::CONVENTIONS, true)) {
            throw new InvalidArgumentException("Unsupported convention: {$convention}.");
        }

        foreach (['amount', 'total_quantity'] as $key) {
            if (! array_key_exists($key, $inputs)) {
                throw new InvalidArgumentException("Missing required input: {$key}.");
            }

            if (! is_numeric($inputs[$key])) {
                throw new InvalidArgumentException("Input '{$key}' must be numeric.");
            }
        }

        $amount = (float) $inputs['amount'];
        $totalQuantity = (float) $inputs['total_quantity'];

        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount of solute must be greater than zero.');
        }

        if ($totalQuantity <= 0) {
            throw new InvalidArgumentException('Total quantity must be greater than zero.');
        }

        $amountUnit = self::normalizeUnit((string) ($inputs['amount_unit'] ?? ''));
        $totalUnit = self::normalizeUnit((string) ($inputs['total_unit'] ?? ''));

        $amountDimension = self::resolveDimension($amountUnit, 'Solute unit');
        $totalDimension = self::resolveDimension($totalUnit, 'Total quantity unit');

        $expectedAmountDimension = $convention === 'v/v' ? 'volume' : 'mass';
        $expectedTotalDimension = $convention === 'w/w' ? 'mass' : 'volume';

        if ($amountDimension !== $expectedAmountDimension) {
            throw new InvalidArgumentException(
                "Solute unit '{$amountUnit}' is not compatible with the {$convention} convention."
            );
        }

        if ($totalDimension !== $expectedTotalDimension) {
            throw new InvalidArgumentException(
                "Total quantity unit '{$totalUnit}' is not compatible with the {$convention} convention."
            );
        }

        $baseAmount = $amount * self::UNITS[$amountUnit]['factor'];
        $baseTotal = $totalQuantity * self::UNITS[$totalUnit]['factor'];

        $percentage = $baseAmount / $baseTotal * 100;

        return [
            'percentage' => $percentage,
        ];
    }

    private static function resolveDimension(string $unit, string $label): string
    {
        if (! isset(self::UNITS[$unit])) {
            throw new InvalidArgumentException("Unsupported {$label}: {$unit}.");
        }

        return self::UNITS[$unit]['dimension'];
    }

    private static function normalizeUnit(string $unit): string
    {
        return mb_strtolower(str_replace(['μ', 'Μ'], 'µ', $unit));
    }
}
