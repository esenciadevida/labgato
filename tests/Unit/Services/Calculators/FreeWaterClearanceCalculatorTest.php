<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\FreeWaterClearanceCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FreeWaterClearanceCalculatorTest extends TestCase
{
    public function test_free_water_clearance_calculates_negative_when_urine_concentrated(): void
    {
        $calculator = new FreeWaterClearanceCalculator;

        $result = $calculator->calculate([
            'urine_osmolality' => 600,
            'serum_osmolality' => 300,
            'urine_volume' => 1,
        ]);

        $this->assertSame(-1.0, $result['free_water_clearance']);
    }

    public function test_free_water_clearance_calculates_positive_when_urine_dilute(): void
    {
        $calculator = new FreeWaterClearanceCalculator;

        $result = $calculator->calculate([
            'urine_osmolality' => 150,
            'serum_osmolality' => 300,
            'urine_volume' => 2,
        ]);

        $this->assertSame(1.0, $result['free_water_clearance']);
    }

    public function test_free_water_clearance_is_zero_at_iso_osmolality(): void
    {
        $calculator = new FreeWaterClearanceCalculator;

        $result = $calculator->calculate([
            'urine_osmolality' => 300,
            'serum_osmolality' => 300,
            'urine_volume' => 2,
        ]);

        $this->assertSame(0.0, $result['free_water_clearance']);
    }

    public function test_free_water_clearance_rejects_zero_serum_osmolality(): void
    {
        $calculator = new FreeWaterClearanceCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_osmolality' => 600,
            'serum_osmolality' => 0,
            'urine_volume' => 1,
        ]);
    }

    public function test_free_water_clearance_requires_all_inputs(): void
    {
        $calculator = new FreeWaterClearanceCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_osmolality' => 600,
            'serum_osmolality' => 300,
        ]);
    }

    public function test_free_water_clearance_rejects_non_numeric_inputs(): void
    {
        $calculator = new FreeWaterClearanceCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_osmolality' => 600,
            'serum_osmolality' => 300,
            'urine_volume' => 'half',
        ]);
    }
}
