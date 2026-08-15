<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\ReticulocyteProductionIndexCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ReticulocyteProductionIndexCalculatorTest extends TestCase
{
    public function test_rpi_calculates_correctly(): void
    {
        $calculator = new ReticulocyteProductionIndexCalculator;

        $result = $calculator->calculate([
            'reticulocyte_percent' => 3,
            'patient_hematocrit' => 30,
            'normal_hematocrit' => 45,
        ]);

        $this->assertSame(1.0, $result['rpi']);
    }

    public function test_rpi_uses_maturation_factor_one_at_normal_hematocrit(): void
    {
        $calculator = new ReticulocyteProductionIndexCalculator;

        $result = $calculator->calculate([
            'reticulocyte_percent' => 4,
            'patient_hematocrit' => 45,
            'normal_hematocrit' => 45,
        ]);

        $this->assertSame(4.0, $result['rpi']);
    }

    public function test_rpi_applies_higher_maturation_factor_in_severe_anemia(): void
    {
        $calculator = new ReticulocyteProductionIndexCalculator;

        $result = $calculator->calculate([
            'reticulocyte_percent' => 5,
            'patient_hematocrit' => 20,
            'normal_hematocrit' => 45,
        ]);

        $this->assertEqualsWithDelta(0.8889, $result['rpi'], 0.0001);
    }

    public function test_rpi_requires_all_inputs(): void
    {
        $calculator = new ReticulocyteProductionIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'reticulocyte_percent' => 3,
            'patient_hematocrit' => 30,
        ]);
    }

    public function test_rpi_rejects_non_numeric_inputs(): void
    {
        $calculator = new ReticulocyteProductionIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'reticulocyte_percent' => 3,
            'patient_hematocrit' => 30,
            'normal_hematocrit' => 'normal',
        ]);
    }
}
