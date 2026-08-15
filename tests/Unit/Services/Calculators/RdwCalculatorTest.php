<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\RdwCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RdwCalculatorTest extends TestCase
{
    public function test_rdw_calculates_correctly(): void
    {
        $calculator = new RdwCalculator;

        $result = $calculator->calculate([
            'rdw_sd' => 45,
            'mcv' => 90,
        ]);

        $this->assertSame(50.0, $result['rdw_cv']);
    }

    public function test_rdw_calculates_correctly_with_decimal_values(): void
    {
        $calculator = new RdwCalculator;

        $result = $calculator->calculate([
            'rdw_sd' => 38.5,
            'mcv' => 92,
        ]);

        $this->assertEqualsWithDelta(41.8478, $result['rdw_cv'], 0.0001);
    }

    public function test_rdw_requires_all_inputs(): void
    {
        $calculator = new RdwCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['rdw_sd' => 45]);
    }

    public function test_rdw_rejects_non_numeric_inputs(): void
    {
        $calculator = new RdwCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'rdw_sd' => 45,
            'mcv' => 'wide',
        ]);
    }
}
