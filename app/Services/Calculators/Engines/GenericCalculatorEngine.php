<?php

namespace App\Services\Calculators\Engines;

use App\Contracts\CalculatorInterface;
use App\Contracts\ToolInterface;

class GenericCalculatorEngine implements CalculatorInterface, ToolInterface
{
    public function calculate(array $inputs): array
    {
        return [];
    }
}
