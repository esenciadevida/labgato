<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\LdlHdlRatioCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LdlHdlRatioCalculatorTest extends TestCase
{
    public function test_ratio_calculates_correctly(): void
    {
        $calculator = new LdlHdlRatioCalculator;

        $result = $calculator->calculate(['ldl' => 120, 'hdl' => 40]);

        $this->assertEqualsWithDelta(3.0, $result['ratio'], 0.001);
    }

    public function test_ratio_rejects_zero_hdl(): void
    {
        $calculator = new LdlHdlRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['ldl' => 120, 'hdl' => 0]);
    }
}
