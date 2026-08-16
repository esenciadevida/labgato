<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\SerialDilutionCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SerialDilutionCalculatorTest extends TestCase
{
    public function test_serial_dilution_calculates_correctly(): void
    {
        $calculator = new SerialDilutionCalculator;

        $result = $calculator->calculate(['initial_concentration' => 100000000, 'dilution_factor' => 10, 'number_of_tubes' => 3]);

        $this->assertEqualsWithDelta(10000000, $result['tube_1'], 1);
        $this->assertEqualsWithDelta(1000000, $result['tube_2'], 1);
        $this->assertEqualsWithDelta(100000, $result['tube_3'], 1);
        $this->assertArrayNotHasKey('tube_4', $result);
    }

    public function test_rejects_too_many_tubes(): void
    {
        $calculator = new SerialDilutionCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['initial_concentration' => 100000000, 'dilution_factor' => 10, 'number_of_tubes' => 7]);
    }

    public function test_rejects_zero_dilution_factor(): void
    {
        $calculator = new SerialDilutionCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['initial_concentration' => 100000000, 'dilution_factor' => 0, 'number_of_tubes' => 3]);
    }
}
