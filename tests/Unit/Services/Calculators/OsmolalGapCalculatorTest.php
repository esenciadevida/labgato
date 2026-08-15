<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\OsmolalGapCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OsmolalGapCalculatorTest extends TestCase
{
    public function test_osmolal_gap_calculates_correctly(): void
    {
        $calculator = new OsmolalGapCalculator;

        $result = $calculator->calculate([
            'measured_osmolality' => 310,
            'sodium' => 140,
            'glucose' => 90,
            'bun' => 14,
        ]);

        $this->assertSame(20.0, $result['osmolal_gap']);
        $this->assertSame(290.0, $result['estimated_osmolality']);
    }

    public function test_osmolal_gap_is_zero_when_matching_estimate(): void
    {
        $calculator = new OsmolalGapCalculator;

        $result = $calculator->calculate([
            'measured_osmolality' => 290,
            'sodium' => 140,
            'glucose' => 90,
            'bun' => 14,
        ]);

        $this->assertSame(0.0, $result['osmolal_gap']);
    }

    public function test_osmolal_gap_requires_all_inputs(): void
    {
        $calculator = new OsmolalGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'measured_osmolality' => 310,
            'sodium' => 140,
            'glucose' => 90,
        ]);
    }

    public function test_osmolal_gap_rejects_non_numeric_inputs(): void
    {
        $calculator = new OsmolalGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'measured_osmolality' => 310,
            'sodium' => 140,
            'glucose' => 90,
            'bun' => 'high',
        ]);
    }
}
