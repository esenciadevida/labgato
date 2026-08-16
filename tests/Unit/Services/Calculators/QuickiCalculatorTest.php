<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\QuickiCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class QuickiCalculatorTest extends TestCase
{
    public function test_quicki_calculates_correctly(): void
    {
        $calculator = new QuickiCalculator;

        $result = $calculator->calculate(['fasting_glucose' => 100, 'fasting_insulin' => 10]);

        $this->assertEqualsWithDelta(0.3333, $result['quicki'], 0.0001);
    }

    public function test_rejects_zero_insulin(): void
    {
        $calculator = new QuickiCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['fasting_glucose' => 100, 'fasting_insulin' => 0]);
    }

    public function test_requires_all_inputs(): void
    {
        $calculator = new QuickiCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['fasting_insulin' => 10]);
    }
}
