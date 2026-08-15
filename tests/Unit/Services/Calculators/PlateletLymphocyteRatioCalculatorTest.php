<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\PlateletLymphocyteRatioCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PlateletLymphocyteRatioCalculatorTest extends TestCase
{
    public function test_plr_calculates_correctly(): void
    {
        $calculator = new PlateletLymphocyteRatioCalculator;

        $result = $calculator->calculate([
            'platelets' => 200,
            'lymphocytes' => 2,
        ]);

        $this->assertSame(100.0, $result['plr']);
    }

    public function test_plr_calculates_correctly_with_decimal_values(): void
    {
        $calculator = new PlateletLymphocyteRatioCalculator;

        $result = $calculator->calculate([
            'platelets' => 350,
            'lymphocytes' => 1.8,
        ]);

        $this->assertEqualsWithDelta(194.4444, $result['plr'], 0.0001);
    }

    public function test_plr_requires_all_inputs(): void
    {
        $calculator = new PlateletLymphocyteRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['platelets' => 200]);
    }

    public function test_plr_rejects_non_numeric_inputs(): void
    {
        $calculator = new PlateletLymphocyteRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'platelets' => 200,
            'lymphocytes' => 'low',
        ]);
    }
}
