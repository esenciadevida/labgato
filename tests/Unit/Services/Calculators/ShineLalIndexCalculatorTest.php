<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\ShineLalIndexCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ShineLalIndexCalculatorTest extends TestCase
{
    public function test_shine_lal_index_calculates_correctly(): void
    {
        $calculator = new ShineLalIndexCalculator;

        $result = $calculator->calculate([
            'mcv' => 60,
            'mch' => 22,
        ]);

        $this->assertSame(792.0, $result['shine_lal_index']);
    }

    public function test_shine_lal_index_below_1530_suggests_thalassemia(): void
    {
        $calculator = new ShineLalIndexCalculator;

        $result = $calculator->calculate([
            'mcv' => 67,
            'mch' => 22.5,
        ]);

        $this->assertEqualsWithDelta(1010.025, $result['shine_lal_index'], 0.0001);
    }

    public function test_shine_lal_index_requires_all_inputs(): void
    {
        $calculator = new ShineLalIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['mcv' => 60]);
    }

    public function test_shine_lal_index_rejects_non_numeric_inputs(): void
    {
        $calculator = new ShineLalIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'mcv' => 60,
            'mch' => 'low',
        ]);
    }
}
