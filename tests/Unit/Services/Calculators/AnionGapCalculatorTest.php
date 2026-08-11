<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\AnionGapCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AnionGapCalculatorTest extends TestCase
{
    public function test_anion_gap_calculates_correctly_with_normal_values(): void
    {
        $calculator = new AnionGapCalculator;

        $result = $calculator->calculate([
            'sodium' => 140,
            'chloride' => 100,
            'bicarbonate' => 24,
        ]);

        $this->assertSame(16.0, $result['anion_gap']);
    }

    public function test_anion_gap_calculates_correctly_with_decimal_values(): void
    {
        $calculator = new AnionGapCalculator;

        $result = $calculator->calculate([
            'sodium' => 141.5,
            'chloride' => 102.3,
            'bicarbonate' => 22.7,
        ]);

        $this->assertSame(16.5, $result['anion_gap']);
    }

    public function test_anion_gap_handles_low_values(): void
    {
        $calculator = new AnionGapCalculator;

        $result = $calculator->calculate([
            'sodium' => 110,
            'chloride' => 85,
            'bicarbonate' => 10,
        ]);

        $this->assertSame(15.0, $result['anion_gap']);
    }

    public function test_anion_gap_requires_all_inputs(): void
    {
        $calculator = new AnionGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sodium' => 140,
            'chloride' => 100,
        ]);
    }

    public function test_anion_gap_rejects_non_numeric_inputs(): void
    {
        $calculator = new AnionGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sodium' => 'high',
            'chloride' => 100,
            'bicarbonate' => 24,
        ]);
    }
}
