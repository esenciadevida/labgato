<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\LdlSampsonCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LdlSampsonCalculatorTest extends TestCase
{
    public function test_ldl_calculates_correctly(): void
    {
        $calculator = new LdlSampsonCalculator;

        $result = $calculator->calculate([
            'total_cholesterol' => 200,
            'hdl' => 50,
            'triglycerides' => 150,
        ]);

        $this->assertEqualsWithDelta(159.45, $result['ldl'], 0.01);
    }

    public function test_ldl_rejects_triglycerides_above_800(): void
    {
        $calculator = new LdlSampsonCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_cholesterol' => 250, 'hdl' => 50, 'triglycerides' => 900]);
    }

    public function test_ldl_requires_all_inputs(): void
    {
        $calculator = new LdlSampsonCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_cholesterol' => 200, 'hdl' => 50]);
    }
}
