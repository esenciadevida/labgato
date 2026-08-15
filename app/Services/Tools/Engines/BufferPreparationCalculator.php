<?php

namespace App\Services\Tools\Engines;

use App\Contracts\ToolInterface;
use InvalidArgumentException;

class BufferPreparationCalculator implements ToolInterface
{
    private const REQUIRED_INPUTS = ['target_ph', 'p_ka', 'total_concentration', 'final_volume'];

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

        $targetPh = (float) $inputs['target_ph'];
        $pKa = (float) $inputs['p_ka'];
        $totalConcentration = (float) $inputs['total_concentration'];
        $finalVolume = (float) $inputs['final_volume'];

        if ($totalConcentration <= 0) {
            throw new InvalidArgumentException('Total buffer concentration must be greater than zero.');
        }

        if ($finalVolume <= 0) {
            throw new InvalidArgumentException('Final volume must be greater than zero.');
        }

        $ratio = 10 ** ($targetPh - $pKa);

        $acidConcentration = $totalConcentration / (1 + $ratio);
        $baseConcentration = $totalConcentration - $acidConcentration;

        return [
            'ratio' => $ratio,
            'acid_concentration' => $acidConcentration,
            'base_concentration' => $baseConcentration,
            'acid_amount' => $acidConcentration * $finalVolume,
            'base_amount' => $baseConcentration * $finalVolume,
        ];
    }
}
