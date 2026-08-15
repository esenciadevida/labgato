<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\RenalFailureIndexCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RenalFailureIndexCalculatorTest extends TestCase
{
    public function test_rfi_calculates_correctly(): void
    {
        $calculator = new RenalFailureIndexCalculator;

        $result = $calculator->calculate([
            'urine_sodium' => 40,
            'serum_creatinine' => 1,
            'urine_creatinine' => 80,
        ]);

        $this->assertSame(0.5, $result['rfi']);
    }

    public function test_rfi_handles_low_fractional_excretion(): void
    {
        $calculator = new RenalFailureIndexCalculator;

        $result = $calculator->calculate([
            'urine_sodium' => 10,
            'serum_creatinine' => 1,
            'urine_creatinine' => 100,
        ]);

        $this->assertSame(0.1, $result['rfi']);
    }

    public function test_rfi_rejects_zero_urine_creatinine(): void
    {
        $calculator = new RenalFailureIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_sodium' => 40,
            'serum_creatinine' => 1,
            'urine_creatinine' => 0,
        ]);
    }

    public function test_rfi_requires_all_inputs(): void
    {
        $calculator = new RenalFailureIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_sodium' => 40,
            'serum_creatinine' => 1,
        ]);
    }

    public function test_rfi_rejects_non_numeric_inputs(): void
    {
        $calculator = new RenalFailureIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'urine_sodium' => 40,
            'serum_creatinine' => 1,
            'urine_creatinine' => 'many',
        ]);
    }
}
