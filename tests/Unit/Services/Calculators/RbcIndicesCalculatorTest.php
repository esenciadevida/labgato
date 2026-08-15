<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\RbcIndicesCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RbcIndicesCalculatorTest extends TestCase
{
    public function test_rbc_indices_calculate_correctly(): void
    {
        $calculator = new RbcIndicesCalculator;

        $result = $calculator->calculate([
            'hematocrit' => 42,
            'hemoglobin' => 14,
            'rbc' => 4.6,
        ]);

        $this->assertEqualsWithDelta(91.3043, $result['mcv'], 0.0001);
        $this->assertEqualsWithDelta(30.4348, $result['mch'], 0.0001);
        $this->assertEqualsWithDelta(33.3333, $result['mchc'], 0.0001);
    }

    public function test_rbc_indices_calculate_clean_values(): void
    {
        $calculator = new RbcIndicesCalculator;

        $result = $calculator->calculate([
            'hematocrit' => 50,
            'hemoglobin' => 15,
            'rbc' => 5,
        ]);

        $this->assertSame(100.0, $result['mcv']);
        $this->assertSame(30.0, $result['mch']);
        $this->assertSame(30.0, $result['mchc']);
    }

    public function test_rbc_indices_require_all_inputs(): void
    {
        $calculator = new RbcIndicesCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'hematocrit' => 42,
            'hemoglobin' => 14,
        ]);
    }

    public function test_rbc_indices_reject_non_numeric_inputs(): void
    {
        $calculator = new RbcIndicesCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'hematocrit' => 42,
            'hemoglobin' => 14,
            'rbc' => 'four',
        ]);
    }
}
