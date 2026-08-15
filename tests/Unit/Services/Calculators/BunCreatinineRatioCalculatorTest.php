<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\BunCreatinineRatioCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BunCreatinineRatioCalculatorTest extends TestCase
{
    public function test_bun_creatinine_ratio_calculates_correctly(): void
    {
        $calculator = new BunCreatinineRatioCalculator;

        $result = $calculator->calculate([
            'bun' => 20,
            'creatinine' => 1,
        ]);

        $this->assertSame(20.0, $result['bun_creatinine_ratio']);
    }

    public function test_bun_creatinine_ratio_handles_decimal_values(): void
    {
        $calculator = new BunCreatinineRatioCalculator;

        $result = $calculator->calculate([
            'bun' => 24,
            'creatinine' => 1.2,
        ]);

        $this->assertSame(20.0, $result['bun_creatinine_ratio']);
    }

    public function test_bun_creatinine_ratio_rejects_zero_creatinine(): void
    {
        $calculator = new BunCreatinineRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'bun' => 20,
            'creatinine' => 0,
        ]);
    }

    public function test_bun_creatinine_ratio_requires_all_inputs(): void
    {
        $calculator = new BunCreatinineRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['bun' => 20]);
    }

    public function test_bun_creatinine_ratio_rejects_non_numeric_inputs(): void
    {
        $calculator = new BunCreatinineRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'bun' => 20,
            'creatinine' => 'high',
        ]);
    }
}
