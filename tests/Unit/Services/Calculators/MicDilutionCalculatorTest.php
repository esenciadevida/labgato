<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\MicDilutionCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MicDilutionCalculatorTest extends TestCase
{
    public function test_mic_dilution_calculates_correctly(): void
    {
        $calculator = new MicDilutionCalculator;

        $result = $calculator->calculate(['stock_concentration' => 128, 'dilution_factor' => 2, 'number_of_wells' => 5]);

        $this->assertEqualsWithDelta(64, $result['well_1'], 0.001);
        $this->assertEqualsWithDelta(32, $result['well_2'], 0.001);
        $this->assertEqualsWithDelta(16, $result['well_3'], 0.001);
        $this->assertEqualsWithDelta(8, $result['well_4'], 0.001);
        $this->assertEqualsWithDelta(4, $result['well_5'], 0.001);
        $this->assertArrayNotHasKey('well_6', $result);
    }

    public function test_rejects_too_many_wells(): void
    {
        $calculator = new MicDilutionCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['stock_concentration' => 128, 'dilution_factor' => 2, 'number_of_wells' => 9]);
    }

    public function test_rejects_zero_dilution_factor(): void
    {
        $calculator = new MicDilutionCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['stock_concentration' => 128, 'dilution_factor' => 0, 'number_of_wells' => 5]);
    }
}
