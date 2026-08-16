<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\HomaBetaCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class HomaBetaCalculatorTest extends TestCase
{
    public function test_homa_beta_calculates_correctly(): void
    {
        $calculator = new HomaBetaCalculator;

        $result = $calculator->calculate(['fasting_glucose' => 100, 'fasting_insulin' => 10]);

        $this->assertEqualsWithDelta(97.2973, $result['homa_beta'], 0.0001);
    }

    public function test_rejects_glucose_at_or_below_63(): void
    {
        $calculator = new HomaBetaCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['fasting_glucose' => 60, 'fasting_insulin' => 10]);
    }

    public function test_requires_all_inputs(): void
    {
        $calculator = new HomaBetaCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['fasting_glucose' => 100]);
    }
}
