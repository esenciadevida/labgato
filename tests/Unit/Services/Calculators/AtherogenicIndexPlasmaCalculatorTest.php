<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\AtherogenicIndexPlasmaCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AtherogenicIndexPlasmaCalculatorTest extends TestCase
{
    public function test_aip_calculates_correctly(): void
    {
        $calculator = new AtherogenicIndexPlasmaCalculator;

        $result = $calculator->calculate(['triglycerides' => 150, 'hdl' => 50]);

        $this->assertEqualsWithDelta(0.4771, $result['aip'], 0.0001);
    }

    public function test_aip_rejects_zero_triglycerides(): void
    {
        $calculator = new AtherogenicIndexPlasmaCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['triglycerides' => 0, 'hdl' => 50]);
    }

    public function test_aip_rejects_zero_hdl(): void
    {
        $calculator = new AtherogenicIndexPlasmaCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['triglycerides' => 150, 'hdl' => 0]);
    }
}
