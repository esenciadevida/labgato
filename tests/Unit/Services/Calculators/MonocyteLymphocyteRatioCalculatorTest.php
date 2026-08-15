<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\MonocyteLymphocyteRatioCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MonocyteLymphocyteRatioCalculatorTest extends TestCase
{
    public function test_mlr_calculates_correctly(): void
    {
        $calculator = new MonocyteLymphocyteRatioCalculator;

        $result = $calculator->calculate([
            'monocytes' => 0.6,
            'lymphocytes' => 2,
        ]);

        $this->assertSame(0.3, $result['mlr']);
    }

    public function test_mlr_calculates_correctly_with_decimal_values(): void
    {
        $calculator = new MonocyteLymphocyteRatioCalculator;

        $result = $calculator->calculate([
            'monocytes' => 0.85,
            'lymphocytes' => 1.7,
        ]);

        $this->assertEqualsWithDelta(0.5, $result['mlr'], 0.0001);
    }

    public function test_mlr_requires_all_inputs(): void
    {
        $calculator = new MonocyteLymphocyteRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['monocytes' => 0.6]);
    }

    public function test_mlr_rejects_non_numeric_inputs(): void
    {
        $calculator = new MonocyteLymphocyteRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'monocytes' => 0.6,
            'lymphocytes' => 'low',
        ]);
    }
}
