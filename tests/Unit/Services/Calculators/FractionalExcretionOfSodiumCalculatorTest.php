<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\FractionalExcretionOfSodiumCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FractionalExcretionOfSodiumCalculatorTest extends TestCase
{
    public function test_fena_calculates_correctly(): void
    {
        $calculator = new FractionalExcretionOfSodiumCalculator;

        $result = $calculator->calculate([
            'urine_sodium' => 40,
            'serum_sodium' => 140,
            'serum_creatinine' => 1,
            'urine_creatinine' => 80,
        ]);

        $this->assertEqualsWithDelta(0.3571, $result['fena'], 0.0001);
    }

    public function test_fena_handles_low_fractional_excretion(): void
    {
        $calculator = new FractionalExcretionOfSodiumCalculator;

        $result = $calculator->calculate([
            'urine_sodium' => 10,
            'serum_sodium' => 140,
            'serum_creatinine' => 1,
            'urine_creatinine' => 100,
        ]);

        $this->assertEqualsWithDelta(0.0714, $result['fena'], 0.0001);
    }

    public function test_fena_rejects_zero_denominator(): void
    {
        $calculator = new FractionalExcretionOfSodiumCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_sodium' => 40,
            'serum_sodium' => 140,
            'serum_creatinine' => 1,
            'urine_creatinine' => 0,
        ]);
    }

    public function test_fena_requires_all_inputs(): void
    {
        $calculator = new FractionalExcretionOfSodiumCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_sodium' => 40,
            'serum_sodium' => 140,
            'serum_creatinine' => 1,
        ]);
    }

    public function test_fena_rejects_non_numeric_inputs(): void
    {
        $calculator = new FractionalExcretionOfSodiumCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_sodium' => 40,
            'serum_sodium' => 140,
            'serum_creatinine' => 1,
            'urine_creatinine' => 'many',
        ]);
    }
}
