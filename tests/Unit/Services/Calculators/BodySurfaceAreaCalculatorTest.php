<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\BodySurfaceAreaCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BodySurfaceAreaCalculatorTest extends TestCase
{
    public function test_bsa_calculates_correctly(): void
    {
        $calculator = new BodySurfaceAreaCalculator;

        $result = $calculator->calculate(['weight' => 70, 'height' => 175]);

        $this->assertEqualsWithDelta(1.8447, $result['bsa_mosteller'], 0.0001);
    }

    public function test_rejects_zero_weight(): void
    {
        $calculator = new BodySurfaceAreaCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['weight' => 0, 'height' => 175]);
    }

    public function test_requires_all_inputs(): void
    {
        $calculator = new BodySurfaceAreaCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['weight' => 70]);
    }
}
