<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\SerumOsmolalityCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SerumOsmolalityCalculatorTest extends TestCase
{
    public function test_serum_osmolality_calculates_correctly(): void
    {
        $calculator = new SerumOsmolalityCalculator;

        $result = $calculator->calculate([
            'sodium' => 140,
            'glucose' => 90,
            'bun' => 14,
        ]);

        $this->assertSame(290.0, $result['estimated_osmolality']);
    }

    public function test_serum_osmolality_calculates_with_elevated_glucose(): void
    {
        $calculator = new SerumOsmolalityCalculator;

        $result = $calculator->calculate([
            'sodium' => 140,
            'glucose' => 500,
            'bun' => 28,
        ]);

        $this->assertEqualsWithDelta(317.7778, $result['estimated_osmolality'], 0.0001);
    }

    public function test_serum_osmolality_requires_all_inputs(): void
    {
        $calculator = new SerumOsmolalityCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sodium' => 140,
            'glucose' => 90,
        ]);
    }

    public function test_serum_osmolality_rejects_non_numeric_inputs(): void
    {
        $calculator = new SerumOsmolalityCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sodium' => 140,
            'glucose' => 90,
            'bun' => 'high',
        ]);
    }
}
