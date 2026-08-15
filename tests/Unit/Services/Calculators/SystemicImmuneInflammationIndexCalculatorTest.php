<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\SystemicImmuneInflammationIndexCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SystemicImmuneInflammationIndexCalculatorTest extends TestCase
{
    public function test_sii_calculates_correctly(): void
    {
        $calculator = new SystemicImmuneInflammationIndexCalculator;

        $result = $calculator->calculate([
            'platelets' => 200,
            'neutrophils' => 5,
            'lymphocytes' => 2,
        ]);

        $this->assertSame(500.0, $result['sii']);
    }

    public function test_sii_calculates_correctly_with_decimal_values(): void
    {
        $calculator = new SystemicImmuneInflammationIndexCalculator;

        $result = $calculator->calculate([
            'platelets' => 280,
            'neutrophils' => 6.5,
            'lymphocytes' => 1.4,
        ]);

        $this->assertEqualsWithDelta(1300.0, $result['sii'], 0.0001);
    }

    public function test_sii_requires_all_inputs(): void
    {
        $calculator = new SystemicImmuneInflammationIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'platelets' => 200,
            'neutrophils' => 5,
        ]);
    }

    public function test_sii_rejects_non_numeric_inputs(): void
    {
        $calculator = new SystemicImmuneInflammationIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'platelets' => 200,
            'neutrophils' => 5,
            'lymphocytes' => 'low',
        ]);
    }
}
