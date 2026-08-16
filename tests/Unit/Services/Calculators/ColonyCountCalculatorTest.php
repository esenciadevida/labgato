<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\ColonyCountCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ColonyCountCalculatorTest extends TestCase
{
    public function test_colony_count_calculates_correctly(): void
    {
        $calculator = new ColonyCountCalculator;

        $result = $calculator->calculate(['cfu_per_ml' => 1000000, 'dilution_factor' => 10000, 'volume_plated' => 0.1]);

        $this->assertEqualsWithDelta(10, $result['colonies'], 0.001);
    }

    public function test_rejects_zero_volume_plated(): void
    {
        $calculator = new ColonyCountCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['cfu_per_ml' => 1000000, 'dilution_factor' => 10000, 'volume_plated' => 0]);
    }

    public function test_requires_all_inputs(): void
    {
        $calculator = new ColonyCountCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['cfu_per_ml' => 1000000, 'dilution_factor' => 10000]);
    }
}
