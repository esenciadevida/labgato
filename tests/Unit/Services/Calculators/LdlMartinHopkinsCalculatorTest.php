<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\LdlMartinHopkinsCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LdlMartinHopkinsCalculatorTest extends TestCase
{
    public function test_ldl_calculates_correctly(): void
    {
        $calculator = new LdlMartinHopkinsCalculator;

        $result = $calculator->calculate([
            'total_cholesterol' => 230,
            'hdl' => 50,
            'triglycerides' => 300,
        ]);

        $this->assertEqualsWithDelta(120, $result['ldl'], 0.001);
    }

    public function test_ldl_with_high_triglycerides_uses_lower_factor(): void
    {
        $calculator = new LdlMartinHopkinsCalculator;

        $result = $calculator->calculate([
            'total_cholesterol' => 250,
            'hdl' => 40,
            'triglycerides' => 500,
        ]);

        $this->assertEqualsWithDelta(138.57, $result['ldl'], 0.01);
    }

    public function test_ldl_rejects_triglycerides_above_800(): void
    {
        $calculator = new LdlMartinHopkinsCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_cholesterol' => 250, 'hdl' => 50, 'triglycerides' => 900]);
    }

    public function test_ldl_requires_all_inputs(): void
    {
        $calculator = new LdlMartinHopkinsCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_cholesterol' => 230, 'hdl' => 50]);
    }
}
