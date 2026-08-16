<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\AntibioticDilutionCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AntibioticDilutionCalculatorTest extends TestCase
{
    public function test_dilution_calculates_correctly(): void
    {
        $calculator = new AntibioticDilutionCalculator;

        $result = $calculator->calculate(['stock_concentration' => 1000, 'desired_concentration' => 100, 'desired_volume' => 10]);

        $this->assertEqualsWithDelta(1.0, $result['stock_volume'], 0.001);
        $this->assertEqualsWithDelta(9.0, $result['solvent_volume'], 0.001);
    }

    public function test_rejects_desired_above_stock(): void
    {
        $calculator = new AntibioticDilutionCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['stock_concentration' => 100, 'desired_concentration' => 200, 'desired_volume' => 10]);
    }

    public function test_rejects_zero_stock(): void
    {
        $calculator = new AntibioticDilutionCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['stock_concentration' => 0, 'desired_concentration' => 100, 'desired_volume' => 10]);
    }
}
