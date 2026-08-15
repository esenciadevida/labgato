<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\CorrectedCalciumCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CorrectedCalciumCalculatorTest extends TestCase
{
    public function test_corrected_calcium_calculates_correctly(): void
    {
        $calculator = new CorrectedCalciumCalculator;

        $result = $calculator->calculate([
            'calcium' => 8,
            'albumin' => 2,
        ]);

        $this->assertSame(9.6, $result['corrected_calcium']);
    }

    public function test_corrected_calcium_is_unchanged_at_albumin_4(): void
    {
        $calculator = new CorrectedCalciumCalculator;

        $result = $calculator->calculate([
            'calcium' => 9.5,
            'albumin' => 4,
        ]);

        $this->assertSame(9.5, $result['corrected_calcium']);
    }

    public function test_corrected_calcium_requires_all_inputs(): void
    {
        $calculator = new CorrectedCalciumCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['calcium' => 8]);
    }

    public function test_corrected_calcium_rejects_non_numeric_inputs(): void
    {
        $calculator = new CorrectedCalciumCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'calcium' => 8,
            'albumin' => 'low',
        ]);
    }
}
