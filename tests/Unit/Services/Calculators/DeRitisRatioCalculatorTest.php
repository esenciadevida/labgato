<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\DeRitisRatioCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DeRitisRatioCalculatorTest extends TestCase
{
    public function test_de_ritis_ratio_over_two_in_alcoholic_liver_disease(): void
    {
        $calculator = new DeRitisRatioCalculator;

        $result = $calculator->calculate([
            'ast' => 100,
            'alt' => 50,
        ]);

        $this->assertSame(2.0, $result['ast_alt_ratio']);
    }

    public function test_de_ritis_ratio_below_one_in_viral_hepatitis(): void
    {
        $calculator = new DeRitisRatioCalculator;

        $result = $calculator->calculate([
            'ast' => 80,
            'alt' => 100,
        ]);

        $this->assertEqualsWithDelta(0.8, $result['ast_alt_ratio'], 0.0001);
    }

    public function test_de_ritis_ratio_of_one_with_equal_enzymes(): void
    {
        $calculator = new DeRitisRatioCalculator;

        $result = $calculator->calculate([
            'ast' => 40,
            'alt' => 40,
        ]);

        $this->assertSame(1.0, $result['ast_alt_ratio']);
    }

    public function test_de_ritis_ratio_rejects_zero_alt(): void
    {
        $calculator = new DeRitisRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['ast' => 40, 'alt' => 0]);
    }

    public function test_de_ritis_ratio_requires_all_inputs(): void
    {
        $calculator = new DeRitisRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['ast' => 40]);
    }
}
