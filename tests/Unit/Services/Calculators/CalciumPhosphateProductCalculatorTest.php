<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\CalciumPhosphateProductCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CalciumPhosphateProductCalculatorTest extends TestCase
{
    public function test_calcium_phosphate_product_multiplies_values(): void
    {
        $calculator = new CalciumPhosphateProductCalculator;

        $result = $calculator->calculate(['calcium' => 10, 'phosphate' => 5]);

        $this->assertSame(50.0, $result['calcium_phosphate_product']);
    }

    public function test_calcium_phosphate_product_handles_decimals(): void
    {
        $calculator = new CalciumPhosphateProductCalculator;

        $result = $calculator->calculate(['calcium' => 9.5, 'phosphate' => 4.2]);

        $this->assertEqualsWithDelta(39.9, $result['calcium_phosphate_product'], 0.01);
    }

    public function test_calcium_phosphate_product_zero_product(): void
    {
        $calculator = new CalciumPhosphateProductCalculator;

        $result = $calculator->calculate(['calcium' => 0, 'phosphate' => 5]);

        $this->assertSame(0.0, $result['calcium_phosphate_product']);
    }

    public function test_calcium_phosphate_product_requires_all_inputs(): void
    {
        $calculator = new CalciumPhosphateProductCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['calcium' => 10]);
    }

    public function test_calcium_phosphate_product_rejects_non_numeric(): void
    {
        $calculator = new CalciumPhosphateProductCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['calcium' => 10, 'phosphate' => 'high']);
    }
}