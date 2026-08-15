<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\MchcCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MchcCalculatorTest extends TestCase
{
    public function test_mchc_calculates_correctly(): void
    {
        $calculator = new MchcCalculator;

        $result = $calculator->calculate([
            'hemoglobin' => 14,
            'hematocrit' => 42,
        ]);

        $this->assertEqualsWithDelta(33.3333, $result['mchc'], 0.0001);
    }

    public function test_mchc_requires_all_inputs(): void
    {
        $calculator = new MchcCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['hemoglobin' => 14]);
    }

    public function test_mchc_rejects_non_numeric_inputs(): void
    {
        $calculator = new MchcCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'hemoglobin' => 14,
            'hematocrit' => 'low',
        ]);
    }
}
