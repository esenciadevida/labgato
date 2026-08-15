<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\FractionalExcretionOfPotassiumCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FractionalExcretionOfPotassiumCalculatorTest extends TestCase
{
    public function test_fek_calculates_correctly(): void
    {
        $calculator = new FractionalExcretionOfPotassiumCalculator;

        $result = $calculator->calculate([
            'urine_potassium' => 20,
            'serum_potassium' => 4,
            'serum_creatinine' => 1,
            'urine_creatinine' => 80,
        ]);

        $this->assertSame(6.25, $result['fek']);
    }

    public function test_fek_handles_low_fractional_excretion(): void
    {
        $calculator = new FractionalExcretionOfPotassiumCalculator;

        $result = $calculator->calculate([
            'urine_potassium' => 10,
            'serum_potassium' => 4,
            'serum_creatinine' => 1,
            'urine_creatinine' => 100,
        ]);

        $this->assertSame(2.5, $result['fek']);
    }

    public function test_fek_rejects_zero_denominator(): void
    {
        $calculator = new FractionalExcretionOfPotassiumCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_potassium' => 20,
            'serum_potassium' => 4,
            'serum_creatinine' => 1,
            'urine_creatinine' => 0,
        ]);
    }

    public function test_fek_requires_all_inputs(): void
    {
        $calculator = new FractionalExcretionOfPotassiumCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_potassium' => 20,
            'serum_potassium' => 4,
            'serum_creatinine' => 1,
        ]);
    }

    public function test_fek_rejects_non_numeric_inputs(): void
    {
        $calculator = new FractionalExcretionOfPotassiumCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_potassium' => 20,
            'serum_potassium' => 4,
            'serum_creatinine' => 1,
            'urine_creatinine' => 'many',
        ]);
    }
}
