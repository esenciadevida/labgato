<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\CorrectedChlorideCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CorrectedChlorideCalculatorTest extends TestCase
{
    public function test_corrected_chloride_calculates_correctly(): void
    {
        $calculator = new CorrectedChlorideCalculator;

        $result = $calculator->calculate([
            'chloride' => 100,
            'sodium' => 150,
        ]);

        $this->assertEqualsWithDelta(93.3333, $result['corrected_chloride'], 0.0001);
    }

    public function test_corrected_chloride_with_low_sodium(): void
    {
        $calculator = new CorrectedChlorideCalculator;

        $result = $calculator->calculate([
            'chloride' => 110,
            'sodium' => 130,
        ]);

        $this->assertEqualsWithDelta(118.4615, $result['corrected_chloride'], 0.0001);
    }

    public function test_corrected_chloride_is_unchanged_at_normal_sodium(): void
    {
        $calculator = new CorrectedChlorideCalculator;

        $result = $calculator->calculate([
            'chloride' => 100,
            'sodium' => 140,
        ]);

        $this->assertSame(100.0, $result['corrected_chloride']);
    }

    public function test_corrected_chloride_rejects_zero_sodium(): void
    {
        $calculator = new CorrectedChlorideCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['chloride' => 100, 'sodium' => 0]);
    }

    public function test_corrected_chloride_requires_all_inputs(): void
    {
        $calculator = new CorrectedChlorideCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['chloride' => 100]);
    }
}
