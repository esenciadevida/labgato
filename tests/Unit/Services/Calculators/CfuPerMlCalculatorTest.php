<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\CfuPerMlCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CfuPerMlCalculatorTest extends TestCase
{
    public function test_cfu_per_ml_calculates_correctly(): void
    {
        $calculator = new CfuPerMlCalculator;

        $result = $calculator->calculate(['colonies' => 100, 'dilution_factor' => 10000, 'volume_plated' => 0.1]);

        $this->assertEqualsWithDelta(1.0e7, $result['cfu_per_ml'], 1);
    }

    public function test_rejects_zero_dilution_factor(): void
    {
        $calculator = new CfuPerMlCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['colonies' => 100, 'dilution_factor' => 0, 'volume_plated' => 0.1]);
    }

    public function test_requires_all_inputs(): void
    {
        $calculator = new CfuPerMlCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['colonies' => 100, 'dilution_factor' => 10000]);
    }
}
