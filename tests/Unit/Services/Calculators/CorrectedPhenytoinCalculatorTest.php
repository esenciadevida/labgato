<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\CorrectedPhenytoinCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CorrectedPhenytoinCalculatorTest extends TestCase
{
    public function test_corrected_phenytoin_applies_sheiner_tozer(): void
    {
        $calculator = new CorrectedPhenytoinCalculator;

        $result = $calculator->calculate([
            'measured_phenytoin' => 10,
            'albumin' => 2.5,
        ]);

        $this->assertEqualsWithDelta(16.666, $result['corrected_phenytoin'], 0.001);
    }

    public function test_corrected_phenytoin_at_normal_albumin(): void
    {
        $calculator = new CorrectedPhenytoinCalculator;

        $result = $calculator->calculate([
            'measured_phenytoin' => 12,
            'albumin' => 4,
        ]);

        $this->assertEqualsWithDelta(13.333, $result['corrected_phenytoin'], 0.001);
    }

    public function test_corrected_phenytoin_requires_all_inputs(): void
    {
        $calculator = new CorrectedPhenytoinCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['measured_phenytoin' => 10]);
    }

    public function test_corrected_phenytoin_rejects_negative_albumin(): void
    {
        $calculator = new CorrectedPhenytoinCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['measured_phenytoin' => 10, 'albumin' => -1]);
    }

    public function test_corrected_phenytoin_rejects_non_numeric_inputs(): void
    {
        $calculator = new CorrectedPhenytoinCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['measured_phenytoin' => 10, 'albumin' => 'low']);
    }
}