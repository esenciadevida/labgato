<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\NeutrophilLymphocyteRatioCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NeutrophilLymphocyteRatioCalculatorTest extends TestCase
{
    public function test_nlr_calculates_correctly(): void
    {
        $calculator = new NeutrophilLymphocyteRatioCalculator;

        $result = $calculator->calculate([
            'neutrophils' => 5,
            'lymphocytes' => 2,
        ]);

        $this->assertSame(2.5, $result['nlr']);
    }

    public function test_nlr_calculates_correctly_with_decimal_values(): void
    {
        $calculator = new NeutrophilLymphocyteRatioCalculator;

        $result = $calculator->calculate([
            'neutrophils' => 6.8,
            'lymphocytes' => 1.6,
        ]);

        $this->assertEqualsWithDelta(4.25, $result['nlr'], 0.0001);
    }

    public function test_nlr_requires_all_inputs(): void
    {
        $calculator = new NeutrophilLymphocyteRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['neutrophils' => 5]);
    }

    public function test_nlr_rejects_non_numeric_inputs(): void
    {
        $calculator = new NeutrophilLymphocyteRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'neutrophils' => 5,
            'lymphocytes' => 'low',
        ]);
    }
}
