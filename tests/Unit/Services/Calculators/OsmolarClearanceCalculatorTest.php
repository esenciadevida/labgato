<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\OsmolarClearanceCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OsmolarClearanceCalculatorTest extends TestCase
{
    public function test_osmolar_clearance_calculates_correctly(): void
    {
        $calculator = new OsmolarClearanceCalculator;

        $result = $calculator->calculate([
            'urine_osmolality' => 600,
            'serum_osmolality' => 300,
            'urine_volume' => 1,
        ]);

        $this->assertSame(2.0, $result['osmolar_clearance']);
    }

    public function test_osmolar_clearance_handles_decimal_volume(): void
    {
        $calculator = new OsmolarClearanceCalculator;

        $result = $calculator->calculate([
            'urine_osmolality' => 600,
            'serum_osmolality' => 300,
            'urine_volume' => 0.5,
        ]);

        $this->assertSame(1.0, $result['osmolar_clearance']);
    }

    public function test_osmolar_clearance_rejects_zero_serum_osmolality(): void
    {
        $calculator = new OsmolarClearanceCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_osmolality' => 600,
            'serum_osmolality' => 0,
            'urine_volume' => 1,
        ]);
    }

    public function test_osmolar_clearance_requires_all_inputs(): void
    {
        $calculator = new OsmolarClearanceCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_osmolality' => 600,
            'serum_osmolality' => 300,
        ]);
    }

    public function test_osmolar_clearance_rejects_non_numeric_inputs(): void
    {
        $calculator = new OsmolarClearanceCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_osmolality' => 600,
            'serum_osmolality' => 300,
            'urine_volume' => 'half',
        ]);
    }
}
