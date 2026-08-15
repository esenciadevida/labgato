<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\BicarbonateGapCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BicarbonateGapCalculatorTest extends TestCase
{
    public function test_bicarbonate_gap_at_normal_values_equals_normal_anion_gap(): void
    {
        $calculator = new BicarbonateGapCalculator;

        $result = $calculator->calculate([
            'anion_gap' => 12,
            'bicarbonate' => 24,
        ]);

        $this->assertSame(12.0, $result['bicarbonate_gap']);
    }

    public function test_bicarbonate_gap_unchanged_in_pure_anion_gap_acidosis(): void
    {
        $calculator = new BicarbonateGapCalculator;

        $result = $calculator->calculate([
            'anion_gap' => 20,
            'bicarbonate' => 16,
        ]);

        $this->assertSame(12.0, $result['bicarbonate_gap']);
    }

    public function test_bicarbonate_gap_excess_suggests_metabolic_alkalosis(): void
    {
        $calculator = new BicarbonateGapCalculator;

        $result = $calculator->calculate([
            'anion_gap' => 20,
            'bicarbonate' => 22,
        ]);

        $this->assertSame(18.0, $result['bicarbonate_gap']);
    }

    public function test_bicarbonate_gap_decreased_suggests_nag_acidosis(): void
    {
        $calculator = new BicarbonateGapCalculator;

        $result = $calculator->calculate([
            'anion_gap' => 20,
            'bicarbonate' => 10,
        ]);

        $this->assertSame(6.0, $result['bicarbonate_gap']);
    }

    public function test_bicarbonate_gap_requires_all_inputs(): void
    {
        $calculator = new BicarbonateGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['anion_gap' => 20]);
    }
}
