<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\BunUreaConverterCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BunUreaConverterCalculatorTest extends TestCase
{
    public function test_bun_converts_to_urea(): void
    {
        $calculator = new BunUreaConverterCalculator;

        $result = $calculator->calculate(['value' => 20, 'unit' => 'bun_mgdl']);

        $this->assertEqualsWithDelta(20, $result['bun_mgdl'], 0.001);
        $this->assertEqualsWithDelta(42.856, $result['urea_mgdl'], 0.01);
        $this->assertEqualsWithDelta(7.14, $result['urea_mmol'], 0.01);
    }

    public function test_urea_mmol_converts_to_bun(): void
    {
        $calculator = new BunUreaConverterCalculator;

        $result = $calculator->calculate(['value' => 7.14, 'unit' => 'urea_mmol']);

        $this->assertEqualsWithDelta(20, $result['bun_mgdl'], 0.01);
    }

    public function test_urea_mgdl_converts_to_bun(): void
    {
        $calculator = new BunUreaConverterCalculator;

        $result = $calculator->calculate(['value' => 42.856, 'unit' => 'urea_mgdl']);

        $this->assertEqualsWithDelta(20, $result['bun_mgdl'], 0.01);
    }

    public function test_rejects_non_positive_value(): void
    {
        $calculator = new BunUreaConverterCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['value' => 0, 'unit' => 'bun_mgdl']);
    }
}
