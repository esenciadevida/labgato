<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\CorrectedReticulocyteCountCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CorrectedReticulocyteCountCalculatorTest extends TestCase
{
    public function test_corrected_reticulocyte_count_calculates_correctly(): void
    {
        $calculator = new CorrectedReticulocyteCountCalculator;

        $result = $calculator->calculate([
            'reticulocyte_percent' => 3,
            'hematocrit' => 30,
        ]);

        $this->assertSame(2.0, $result['corrected_reticulocyte_count']);
    }

    public function test_corrected_reticulocyte_count_is_unchanged_at_normal_hematocrit(): void
    {
        $calculator = new CorrectedReticulocyteCountCalculator;

        $result = $calculator->calculate([
            'reticulocyte_percent' => 1.5,
            'hematocrit' => 45,
        ]);

        $this->assertSame(1.5, $result['corrected_reticulocyte_count']);
    }

    public function test_corrected_reticulocyte_count_requires_all_inputs(): void
    {
        $calculator = new CorrectedReticulocyteCountCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['reticulocyte_percent' => 3]);
    }

    public function test_corrected_reticulocyte_count_rejects_non_numeric_inputs(): void
    {
        $calculator = new CorrectedReticulocyteCountCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'reticulocyte_percent' => 3,
            'hematocrit' => 'low',
        ]);
    }
}
