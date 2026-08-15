<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\CorrectedSodiumCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CorrectedSodiumCalculatorTest extends TestCase
{
    public function test_corrected_sodium_calculates_correctly(): void
    {
        $calculator = new CorrectedSodiumCalculator;

        $result = $calculator->calculate([
            'sodium' => 130,
            'glucose' => 500,
        ]);

        $this->assertSame(136.4, $result['corrected_sodium']);
    }

    public function test_corrected_sodium_is_unchanged_at_glucose_100(): void
    {
        $calculator = new CorrectedSodiumCalculator;

        $result = $calculator->calculate([
            'sodium' => 130,
            'glucose' => 100,
        ]);

        $this->assertSame(130.0, $result['corrected_sodium']);
    }

    public function test_corrected_sodium_requires_all_inputs(): void
    {
        $calculator = new CorrectedSodiumCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['sodium' => 130]);
    }

    public function test_corrected_sodium_rejects_non_numeric_inputs(): void
    {
        $calculator = new CorrectedSodiumCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sodium' => 130,
            'glucose' => 'high',
        ]);
    }
}
