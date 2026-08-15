<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\CorrectedWbcCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CorrectedWbcCalculatorTest extends TestCase
{
    public function test_corrected_wbc_calculates_correctly(): void
    {
        $calculator = new CorrectedWbcCalculator;

        $result = $calculator->calculate([
            'reported_wbc' => 12,
            'nrbc_per_100_wbc' => 20,
        ]);

        $this->assertSame(10.0, $result['corrected_wbc']);
    }

    public function test_corrected_wbc_is_unchanged_without_nrbc(): void
    {
        $calculator = new CorrectedWbcCalculator;

        $result = $calculator->calculate([
            'reported_wbc' => 8,
            'nrbc_per_100_wbc' => 0,
        ]);

        $this->assertSame(8.0, $result['corrected_wbc']);
    }

    public function test_corrected_wbc_requires_all_inputs(): void
    {
        $calculator = new CorrectedWbcCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['reported_wbc' => 12]);
    }

    public function test_corrected_wbc_rejects_non_numeric_inputs(): void
    {
        $calculator = new CorrectedWbcCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'reported_wbc' => 12,
            'nrbc_per_100_wbc' => 'many',
        ]);
    }
}
