<?php

namespace App\Contracts;

interface CalculatorInterface
{
    public function calculate(array $inputs): array;
}
