<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\RdwIndexCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RdwIndexCalculatorTest extends TestCase
{
    public function test_rdw_index_calculates_correctly(): void
    {
        $calculator = new RdwIndexCalculator;

        $result = $calculator->calculate([
            'mcv' => 60,
            'rdw' => 15,
            'rbc' => 5,
        ]);

        $this->assertSame(180.0, $result['rdw_index']);
    }

    public function test_rdw_index_calculates_correctly_with_decimal_values(): void
    {
        $calculator = new RdwIndexCalculator;

        $result = $calculator->calculate([
            'mcv' => 65.3,
            'rdw' => 16.2,
            'rbc' => 4.8,
        ]);

        $this->assertEqualsWithDelta(220.3875, $result['rdw_index'], 0.0001);
    }

    public function test_rdw_index_requires_all_inputs(): void
    {
        $calculator = new RdwIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'mcv' => 60,
            'rdw' => 15,
        ]);
    }

    public function test_rdw_index_rejects_non_numeric_inputs(): void
    {
        $calculator = new RdwIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'mcv' => 60,
            'rdw' => 15,
            'rbc' => 'many',
        ]);
    }
}
