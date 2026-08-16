<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\TotalCholesterolHdlRatioCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TotalCholesterolHdlRatioCalculatorTest extends TestCase
{
    public function test_ratio_calculates_correctly(): void
    {
        $calculator = new TotalCholesterolHdlRatioCalculator;

        $result = $calculator->calculate(['total_cholesterol' => 200, 'hdl' => 50]);

        $this->assertEqualsWithDelta(4.0, $result['ratio'], 0.001);
    }

    public function test_ratio_rejects_zero_hdl(): void
    {
        $calculator = new TotalCholesterolHdlRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_cholesterol' => 200, 'hdl' => 0]);
    }

    public function test_ratio_requires_all_inputs(): void
    {
        $calculator = new TotalCholesterolHdlRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_cholesterol' => 200]);
    }
}
