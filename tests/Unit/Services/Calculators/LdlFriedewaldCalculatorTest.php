<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\LdlFriedewaldCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LdlFriedewaldCalculatorTest extends TestCase
{
    public function test_ldl_calculates_correctly(): void
    {
        $calculator = new LdlFriedewaldCalculator;

        $result = $calculator->calculate([
            'total_cholesterol' => 200,
            'hdl' => 50,
            'triglycerides' => 150,
        ]);

        $this->assertEqualsWithDelta(120, $result['ldl'], 0.001);
    }

    public function test_ldl_rejects_triglycerides_above_400(): void
    {
        $calculator = new LdlFriedewaldCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_cholesterol' => 250, 'hdl' => 50, 'triglycerides' => 450]);
    }

    public function test_ldl_requires_all_inputs(): void
    {
        $calculator = new LdlFriedewaldCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_cholesterol' => 200, 'hdl' => 50]);
    }
}
