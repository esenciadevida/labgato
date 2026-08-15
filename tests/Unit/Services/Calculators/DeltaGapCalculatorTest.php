<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\DeltaGapCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DeltaGapCalculatorTest extends TestCase
{
    public function test_delta_gap_calculates_correctly(): void
    {
        $calculator = new DeltaGapCalculator;

        $result = $calculator->calculate([
            'anion_gap' => 20,
            'normal_gap' => 12,
        ]);

        $this->assertSame(8.0, $result['delta_gap']);
    }

    public function test_delta_gap_is_zero_at_normal_gap(): void
    {
        $calculator = new DeltaGapCalculator;

        $result = $calculator->calculate([
            'anion_gap' => 12,
            'normal_gap' => 12,
        ]);

        $this->assertSame(0.0, $result['delta_gap']);
    }

    public function test_delta_gap_requires_all_inputs(): void
    {
        $calculator = new DeltaGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['anion_gap' => 20]);
    }

    public function test_delta_gap_rejects_non_numeric_inputs(): void
    {
        $calculator = new DeltaGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'anion_gap' => 20,
            'normal_gap' => 'twelve',
        ]);
    }
}
