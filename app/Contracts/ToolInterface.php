<?php

namespace App\Contracts;

interface ToolInterface
{
    public function calculate(array $inputs): array;
}
