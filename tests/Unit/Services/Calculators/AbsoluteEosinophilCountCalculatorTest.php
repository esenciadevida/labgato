<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\AbsoluteEosinophilCountCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AbsoluteEosinophilCountCalculatorTest extends TestCase
{
    public function test_aec_calculates_correctly_with_normal_values(): void
    {
        $calculator = new AbsoluteEosinophilCountCalculator;

        $result = $calculator->calculate([
            'wbc' => 8,
            'eosinophils' => 4,
        ]);

        $this->assertEqualsWithDelta(0.32, $result['aec'], 0.0001);
    }

    public function test_aec_calculates_correctly_with_zero_eosinophils(): void
    {
        $calculator = new AbsoluteEosinophilCountCalculator;

        $result = $calculator->calculate([
            'wbc' => 6,
            'eosinophils' => 0,
        ]);

        $this->assertSame(0.0, $result['aec']);
    }

    public function test_aec_requires_all_inputs(): void
    {
        $calculator = new AbsoluteEosinophilCountCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['eosinophils' => 4]);
    }

    public function test_aec_rejects_non_numeric_inputs(): void
    {
        $calculator = new AbsoluteEosinophilCountCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'wbc' => 8,
            'eosinophils' => 'many',
        ]);
    }
}
