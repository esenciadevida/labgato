<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\McFarlandTurbidityCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class McFarlandTurbidityCalculatorTest extends TestCase
{
    public function test_mcfarland_converts_to_cfu(): void
    {
        $calculator = new McFarlandTurbidityCalculator;

        $result = $calculator->calculate(['mcfarland' => 0.5]);

        $this->assertEqualsWithDelta(1.5e8, $result['cfu_per_ml'], 1);
    }

    public function test_rejects_zero_mcfarland(): void
    {
        $calculator = new McFarlandTurbidityCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['mcfarland' => 0]);
    }

    public function test_requires_input(): void
    {
        $calculator = new McFarlandTurbidityCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([]);
    }
}
