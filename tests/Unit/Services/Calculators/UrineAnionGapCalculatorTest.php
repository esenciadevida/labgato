<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\UrineAnionGapCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UrineAnionGapCalculatorTest extends TestCase
{
    public function test_urine_anion_gap_calculates_correctly(): void
    {
        $calculator = new UrineAnionGapCalculator;

        $result = $calculator->calculate([
            'urine_sodium' => 40,
            'urine_potassium' => 30,
            'urine_chloride' => 60,
        ]);

        $this->assertSame(10.0, $result['urine_anion_gap']);
    }

    public function test_urine_anion_gap_can_be_negative(): void
    {
        $calculator = new UrineAnionGapCalculator;

        $result = $calculator->calculate([
            'urine_sodium' => 40,
            'urine_potassium' => 30,
            'urine_chloride' => 90,
        ]);

        $this->assertSame(-20.0, $result['urine_anion_gap']);
    }

    public function test_urine_anion_gap_requires_all_inputs(): void
    {
        $calculator = new UrineAnionGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_sodium' => 40,
            'urine_potassium' => 30,
        ]);
    }

    public function test_urine_anion_gap_rejects_non_numeric_inputs(): void
    {
        $calculator = new UrineAnionGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_sodium' => 40,
            'urine_potassium' => 30,
            'urine_chloride' => 'low',
        ]);
    }
}
