<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\NonHdlCholesterolCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NonHdlCholesterolCalculatorTest extends TestCase
{
    public function test_non_hdl_calculates_correctly(): void
    {
        $calculator = new NonHdlCholesterolCalculator;

        $result = $calculator->calculate(['total_cholesterol' => 200, 'hdl' => 50]);

        $this->assertEqualsWithDelta(150, $result['non_hdl'], 0.001);
    }

    public function test_non_hdl_requires_all_inputs(): void
    {
        $calculator = new NonHdlCholesterolCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_cholesterol' => 200]);
    }
}
