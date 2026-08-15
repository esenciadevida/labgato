<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\FractionalExcretionOfUreaCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FractionalExcretionOfUreaCalculatorTest extends TestCase
{
    public function test_feu_calculates_correctly(): void
    {
        $calculator = new FractionalExcretionOfUreaCalculator;

        $result = $calculator->calculate([
            'urine_urea' => 200,
            'serum_urea' => 20,
            'serum_creatinine' => 1,
            'urine_creatinine' => 80,
        ]);

        $this->assertSame(12.5, $result['feurea']);
    }

    public function test_feu_handles_low_fractional_excretion(): void
    {
        $calculator = new FractionalExcretionOfUreaCalculator;

        $result = $calculator->calculate([
            'urine_urea' => 50,
            'serum_urea' => 25,
            'serum_creatinine' => 1,
            'urine_creatinine' => 100,
        ]);

        $this->assertSame(2.0, $result['feurea']);
    }

    public function test_feu_rejects_zero_denominator(): void
    {
        $calculator = new FractionalExcretionOfUreaCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_urea' => 200,
            'serum_urea' => 20,
            'serum_creatinine' => 1,
            'urine_creatinine' => 0,
        ]);
    }

    public function test_feu_requires_all_inputs(): void
    {
        $calculator = new FractionalExcretionOfUreaCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_urea' => 200,
            'serum_urea' => 20,
            'serum_creatinine' => 1,
        ]);
    }

    public function test_feu_rejects_non_numeric_inputs(): void
    {
        $calculator = new FractionalExcretionOfUreaCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_urea' => 200,
            'serum_urea' => 20,
            'serum_creatinine' => 1,
            'urine_creatinine' => 'many',
        ]);
    }
}
