<?php

namespace App\Services\Tools\Engines;

use App\Contracts\ToolInterface;
use InvalidArgumentException;

class RpmToRcfConverter implements ToolInterface
{
    private const RCF_CONSTANT = 1.118e-5;

    private const REQUIRED_INPUTS = ['mode', 'value', 'radius'];

    private const MODES = ['rpm_to_rcf', 'rcf_to_rpm'];

    public function calculate(array $inputs): array
    {
        foreach (self::REQUIRED_INPUTS as $key) {
            if (! array_key_exists($key, $inputs)) {
                throw new InvalidArgumentException("Missing required input: {$key}.");
            }
        }

        $mode = (string) $inputs['mode'];

        if (! in_array($mode, self::MODES, true)) {
            throw new InvalidArgumentException("Unsupported conversion mode: {$mode}.");
        }

        $value = (float) $inputs['value'];
        $radius = (float) $inputs['radius'];

        if ($value < 0) {
            throw new InvalidArgumentException('Value cannot be negative.');
        }

        if ($radius <= 0) {
            throw new InvalidArgumentException('Rotor radius must be greater than zero.');
        }

        if ($mode === 'rcf_to_rpm') {
            $result = sqrt($value / (self::RCF_CONSTANT * $radius));
        } else {
            $result = self::RCF_CONSTANT * $radius * ($value * $value);
        }

        return [
            'result' => $result,
        ];
    }
}
