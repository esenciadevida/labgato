<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\AlbuminGlobulinRatioCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AlbuminGlobulinRatioCalculatorTest extends TestCase
{
    public function test_ag_ratio_calculates_correctly(): void
    {
        $calculator = new AlbuminGlobulinRatioCalculator;

        $result = $calculator->calculate([
            'albumin' => 4.0,
            'total_protein' => 7.0,
        ]);

        $this->assertEqualsWithDelta(1.3333, $result['ag_ratio'], 0.0001);
    }

    public function test_ag_ratio_reversed_in_high_globulin(): void
    {
        $calculator = new AlbuminGlobulinRatioCalculator;

        $result = $calculator->calculate([
            'albumin' => 2.5,
            'total_protein' => 9.0,
        ]);

        $this->assertEqualsWithDelta(0.3846, $result['ag_ratio'], 0.0001);
    }

    public function test_ag_ratio_rejects_zero_globulin(): void
    {
        $calculator = new AlbuminGlobulinRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['albumin' => 7.0, 'total_protein' => 7.0]);
    }

    public function test_ag_ratio_requires_all_inputs(): void
    {
        $calculator = new AlbuminGlobulinRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['albumin' => 4.0]);
    }
}
