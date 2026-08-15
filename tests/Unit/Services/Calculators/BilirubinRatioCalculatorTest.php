<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\BilirubinRatioCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BilirubinRatioCalculatorTest extends TestCase
{
    public function test_bilirubin_ratio_calculates_indirect_and_direct_ratio(): void
    {
        $calculator = new BilirubinRatioCalculator;

        $result = $calculator->calculate([
            'total_bilirubin' => 1.2,
            'direct_bilirubin' => 0.3,
        ]);

        $this->assertEqualsWithDelta(0.9, $result['indirect_bilirubin'], 0.0001);
        $this->assertSame(25.0, $result['direct_total_ratio']);
    }

    public function test_bilirubin_ratio_high_direct_percentage(): void
    {
        $calculator = new BilirubinRatioCalculator;

        $result = $calculator->calculate([
            'total_bilirubin' => 3.0,
            'direct_bilirubin' => 1.6,
        ]);

        $this->assertEqualsWithDelta(1.4, $result['indirect_bilirubin'], 0.0001);
        $this->assertEqualsWithDelta(53.3333, $result['direct_total_ratio'], 0.0001);
    }

    public function test_bilirubin_ratio_low_direct_percentage(): void
    {
        $calculator = new BilirubinRatioCalculator;

        $result = $calculator->calculate([
            'total_bilirubin' => 2.4,
            'direct_bilirubin' => 0.2,
        ]);

        $this->assertEqualsWithDelta(2.2, $result['indirect_bilirubin'], 0.0001);
        $this->assertEqualsWithDelta(8.3333, $result['direct_total_ratio'], 0.0001);
    }

    public function test_bilirubin_ratio_rejects_zero_total(): void
    {
        $calculator = new BilirubinRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_bilirubin' => 0, 'direct_bilirubin' => 0.1]);
    }

    public function test_bilirubin_ratio_rejects_direct_above_total(): void
    {
        $calculator = new BilirubinRatioCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_bilirubin' => 1.0, 'direct_bilirubin' => 1.5]);
    }
}
