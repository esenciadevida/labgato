<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\BacterialConcentrationCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BacterialConcentrationCalculatorTest extends TestCase
{
    public function test_concentration_calculates_correctly(): void
    {
        $calculator = new BacterialConcentrationCalculator;

        $result = $calculator->calculate(['od600' => 0.5, 'conversion_factor' => 100000000]);

        $this->assertEqualsWithDelta(5.0e7, $result['cfu_per_ml'], 1);
    }

    public function test_zero_od600_returns_zero(): void
    {
        $calculator = new BacterialConcentrationCalculator;

        $result = $calculator->calculate(['od600' => 0, 'conversion_factor' => 100000000]);

        $this->assertEqualsWithDelta(0, $result['cfu_per_ml'], 0.001);
    }

    public function test_rejects_negative_od600(): void
    {
        $calculator = new BacterialConcentrationCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['od600' => -0.1, 'conversion_factor' => 100000000]);
    }
}
