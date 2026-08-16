<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\TriglycerideHdlRatioCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TriglycerideHdlRatioCalculatorTest extends TestCase
{
    public function test_ratio_calculates_correctly(): void
    {
        $calculator = new TriglycerideHdlRatioCalculator;

        $result = $calculator->calculate(['triglycerides' => 150, 'hdl' => 50]);

        $this->assertEqualsWithDelta(3.0, $result['ratio'], 0.001);
    }

    public function test_ratio_rejects_zero_hdl(): void
    {
        $calculator = new TriglycerideHdlRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['triglycerides' => 150, 'hdl' => 0]);
    }
}
