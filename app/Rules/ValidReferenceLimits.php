<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidReferenceLimits implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = request()->all();

        $toNumber = function (mixed $input): ?float {
            if ($input === null || $input === '') {
                return null;
            }

            return is_numeric($input) ? (float) $input : null;
        };

        $lower = $toNumber($data['lower_limit'] ?? null);
        $upper = $toNumber($data['upper_limit'] ?? null);
        $criticalLow = $toNumber($data['critical_low'] ?? null);
        $criticalHigh = $toNumber($data['critical_high'] ?? null);
        $ageMin = $toNumber($data['age_min'] ?? null);
        $ageMax = $toNumber($data['age_max'] ?? null);

        if ($lower !== null && $upper !== null && $lower >= $upper) {
            $fail('The lower limit must be less than the upper limit.');
        }

        if ($criticalLow !== null && $lower !== null && $criticalLow >= $lower) {
            $fail('The critical low value must be less than the lower limit.');
        }

        if ($criticalHigh !== null && $upper !== null && $criticalHigh <= $upper) {
            $fail('The critical high value must be greater than the upper limit.');
        }

        if ($ageMin !== null && $ageMax !== null && $ageMin > $ageMax) {
            $fail('The minimum age must be less than or equal to the maximum age.');
        }
    }
}
