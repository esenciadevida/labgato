<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\ReticulocyteIndexCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ReticulocyteIndexCalculatorTest extends TestCase
{
    public function test_reticulocyte_index_calculates_correctly(): void
    {
        $calculator = new ReticulocyteIndexCalculator;

        $result = $calculator->calculate([
            'reticulocyte_percent' => 3,
            'hematocrit' => 30,
        ]);

        $this->assertSame(2.0, $result['reticulocyte_index']);
    }

    public function test_reticulocyte_index_is_unchanged_at_normal_hematocrit(): void
    {
        $calculator = new ReticulocyteIndexCalculator;

        $result = $calculator->calculate([
            'reticulocyte_percent' => 2.5,
            'hematocrit' => 45,
        ]);

        $this->assertSame(2.5, $result['reticulocyte_index']);
    }

    public function test_reticulocyte_index_requires_all_inputs(): void
    {
        $calculator = new ReticulocyteIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['hematocrit' => 30]);
    }

    public function test_reticulocyte_index_rejects_non_numeric_inputs(): void
    {
        $calculator = new ReticulocyteIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'reticulocyte_percent' => 3,
            'hematocrit' => 'low',
        ]);
    }
}
