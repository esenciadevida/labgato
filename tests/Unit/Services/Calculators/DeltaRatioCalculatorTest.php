<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\DeltaRatioCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DeltaRatioCalculatorTest extends TestCase
{
    public function test_delta_ratio_calculates_correctly(): void
    {
        $calculator = new DeltaRatioCalculator;

        $result = $calculator->calculate([
            'anion_gap' => 20,
            'bicarbonate' => 10,
        ]);

        $this->assertEqualsWithDelta(0.5714, $result['delta_ratio'], 0.0001);
    }

    public function test_delta_ratio_handles_high_anion_gap(): void
    {
        $calculator = new DeltaRatioCalculator;

        $result = $calculator->calculate([
            'anion_gap' => 30,
            'bicarbonate' => 8,
        ]);

        $this->assertEqualsWithDelta(1.125, $result['delta_ratio'], 0.0001);
    }

    public function test_delta_ratio_is_undefined_at_bicarbonate_24(): void
    {
        $calculator = new DeltaRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'anion_gap' => 20,
            'bicarbonate' => 24,
        ]);
    }

    public function test_delta_ratio_requires_all_inputs(): void
    {
        $calculator = new DeltaRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['anion_gap' => 20]);
    }

    public function test_delta_ratio_rejects_non_numeric_inputs(): void
    {
        $calculator = new DeltaRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'anion_gap' => 20,
            'bicarbonate' => 'ten',
        ]);
    }
}
