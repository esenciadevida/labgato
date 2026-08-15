<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\CorrectedTotalProteinCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CorrectedTotalProteinCalculatorTest extends TestCase
{
    public function test_corrected_total_protein_calculates_correctly(): void
    {
        $calculator = new CorrectedTotalProteinCalculator;

        $result = $calculator->calculate([
            'total_protein' => 7.0,
            'fibrinogen' => 0.4,
        ]);

        $this->assertEqualsWithDelta(6.6, $result['corrected_total_protein'], 0.0001);
    }

    public function test_corrected_total_protein_with_higher_fibrinogen(): void
    {
        $calculator = new CorrectedTotalProteinCalculator;

        $result = $calculator->calculate([
            'total_protein' => 6.0,
            'fibrinogen' => 0.5,
        ]);

        $this->assertSame(5.5, $result['corrected_total_protein']);
    }

    public function test_corrected_total_protein_rejects_fibrinogen_above_total_protein(): void
    {
        $calculator = new CorrectedTotalProteinCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_protein' => 7.0, 'fibrinogen' => 8.0]);
    }

    public function test_corrected_total_protein_requires_all_inputs(): void
    {
        $calculator = new CorrectedTotalProteinCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['total_protein' => 7.0]);
    }
}
