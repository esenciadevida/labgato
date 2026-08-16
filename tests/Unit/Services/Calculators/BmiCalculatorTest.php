<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\BmiCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BmiCalculatorTest extends TestCase
{
    public function test_bmi_calculates_correctly(): void
    {
        $calculator = new BmiCalculator;

        $result = $calculator->calculate(['weight' => 70, 'height' => 175]);

        $this->assertEqualsWithDelta(22.8571, $result['bmi'], 0.0001);
    }

    public function test_rejects_zero_height(): void
    {
        $calculator = new BmiCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['weight' => 70, 'height' => 0]);
    }

    public function test_requires_all_inputs(): void
    {
        $calculator = new BmiCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['weight' => 70]);
    }
}
