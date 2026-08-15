<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\McvCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class McvCalculatorTest extends TestCase
{
    public function test_mcv_calculates_correctly(): void
    {
        $calculator = new McvCalculator;

        $result = $calculator->calculate([
            'hematocrit' => 42,
            'rbc' => 5,
        ]);

        $this->assertSame(84.0, $result['mcv']);
    }

    public function test_mcv_requires_all_inputs(): void
    {
        $calculator = new McvCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['hematocrit' => 42]);
    }

    public function test_mcv_rejects_non_numeric_inputs(): void
    {
        $calculator = new McvCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'hematocrit' => 42,
            'rbc' => 'five',
        ]);
    }
}
