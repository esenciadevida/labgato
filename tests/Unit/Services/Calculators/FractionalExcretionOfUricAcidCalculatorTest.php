<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\FractionalExcretionOfUricAcidCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FractionalExcretionOfUricAcidCalculatorTest extends TestCase
{
    public function test_feua_calculates_correctly(): void
    {
        $calculator = new FractionalExcretionOfUricAcidCalculator;

        $result = $calculator->calculate([
            'urine_uric_acid' => 20,
            'serum_uric_acid' => 5,
            'serum_creatinine' => 1,
            'urine_creatinine' => 80,
        ]);

        $this->assertSame(5.0, $result['feua']);
    }

    public function test_feua_handles_decimal_values(): void
    {
        $calculator = new FractionalExcretionOfUricAcidCalculator;

        $result = $calculator->calculate([
            'urine_uric_acid' => 25,
            'serum_uric_acid' => 6,
            'serum_creatinine' => 1.2,
            'urine_creatinine' => 100,
        ]);

        $this->assertSame(5.0, $result['feua']);
    }

    public function test_feua_rejects_zero_denominator(): void
    {
        $calculator = new FractionalExcretionOfUricAcidCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_uric_acid' => 20,
            'serum_uric_acid' => 5,
            'serum_creatinine' => 1,
            'urine_creatinine' => 0,
        ]);
    }

    public function test_feua_requires_all_inputs(): void
    {
        $calculator = new FractionalExcretionOfUricAcidCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_uric_acid' => 20,
            'serum_uric_acid' => 5,
            'serum_creatinine' => 1,
        ]);
    }

    public function test_feua_rejects_non_numeric_inputs(): void
    {
        $calculator = new FractionalExcretionOfUricAcidCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_uric_acid' => 20,
            'serum_uric_acid' => 5,
            'serum_creatinine' => 1,
            'urine_creatinine' => 'many',
        ]);
    }
}
