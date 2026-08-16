<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\HomaIrCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class HomaIrCalculatorTest extends TestCase
{
    public function test_homa_ir_calculates_correctly(): void
    {
        $calculator = new HomaIrCalculator;

        $result = $calculator->calculate(['fasting_glucose' => 100, 'fasting_insulin' => 10]);

        $this->assertEqualsWithDelta(2.4691, $result['homa_ir'], 0.0001);
    }

    public function test_rejects_zero_glucose(): void
    {
        $calculator = new HomaIrCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['fasting_glucose' => 0, 'fasting_insulin' => 10]);
    }

    public function test_requires_all_inputs(): void
    {
        $calculator = new HomaIrCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['fasting_glucose' => 100]);
    }
}
