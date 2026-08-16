<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\GlucoseUnitConversionCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GlucoseUnitConversionCalculatorTest extends TestCase
{
    public function test_mgdl_converts_to_mmol(): void
    {
        $calculator = new GlucoseUnitConversionCalculator;

        $result = $calculator->calculate(['value' => 100, 'unit' => 'mgdl']);

        $this->assertEqualsWithDelta(100, $result['glucose_mgdl'], 0.001);
        $this->assertEqualsWithDelta(5.55, $result['glucose_mmol'], 0.01);
    }

    public function test_mmol_converts_to_mgdl(): void
    {
        $calculator = new GlucoseUnitConversionCalculator;

        $result = $calculator->calculate(['value' => 5.55, 'unit' => 'mmol']);

        $this->assertEqualsWithDelta(100, $result['glucose_mgdl'], 0.01);
    }

    public function test_rejects_non_positive_value(): void
    {
        $calculator = new GlucoseUnitConversionCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['value' => 0, 'unit' => 'mgdl']);
    }
}
