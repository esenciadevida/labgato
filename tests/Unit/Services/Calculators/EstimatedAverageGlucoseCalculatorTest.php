<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\EstimatedAverageGlucoseCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EstimatedAverageGlucoseCalculatorTest extends TestCase
{
    public function test_eag_calculates_correctly(): void
    {
        $calculator = new EstimatedAverageGlucoseCalculator;

        $result = $calculator->calculate(['hba1c' => 7]);

        $this->assertEqualsWithDelta(154.2, $result['eag_mgdl'], 0.01);
        $this->assertEqualsWithDelta(8.56, $result['eag_mmol'], 0.01);
    }

    public function test_rejects_zero_hba1c(): void
    {
        $calculator = new EstimatedAverageGlucoseCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['hba1c' => 0]);
    }

    public function test_requires_input(): void
    {
        $calculator = new EstimatedAverageGlucoseCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([]);
    }
}
