<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\GlobulinCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GlobulinCalculatorTest extends TestCase
{
    public function test_globulin_calculates_correctly(): void
    {
        $calculator = new GlobulinCalculator;

        $result = $calculator->calculate([
            'total_protein' => 7.0,
            'albumin' => 4.0,
        ]);

        $this->assertSame(3.0, $result['globulin']);
    }

    public function test_globulin_calculates_with_decimals(): void
    {
        $calculator = new GlobulinCalculator;

        $result = $calculator->calculate([
            'total_protein' => 6.0,
            'albumin' => 2.5,
        ]);

        $this->assertSame(3.5, $result['globulin']);
    }

    public function test_globulin_requires_all_inputs(): void
    {
        $calculator = new GlobulinCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_protein' => 7.0]);
    }
}
