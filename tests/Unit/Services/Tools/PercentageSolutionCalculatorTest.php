<?php

namespace Tests\Unit\Services\Tools;

use App\Services\Tools\Engines\PercentageSolutionCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PercentageSolutionCalculatorTest extends TestCase
{
    private PercentageSolutionCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new PercentageSolutionCalculator;
    }

    public function test_calculates_wv_percentage(): void
    {
        $result = $this->calculator->calculate([
            'convention' => 'w/v',
            'amount' => '0.9',
            'amount_unit' => 'g',
            'total_quantity' => '100',
            'total_unit' => 'mL',
        ]);

        $this->assertEqualsWithDelta(0.9, $result['percentage'], 1e-9);
    }

    public function test_calculates_vv_percentage(): void
    {
        $result = $this->calculator->calculate([
            'convention' => 'v/v',
            'amount' => '5',
            'amount_unit' => 'mL',
            'total_quantity' => '250',
            'total_unit' => 'mL',
        ]);

        $this->assertSame(2.0, $result['percentage']);
    }

    public function test_calculates_ww_percentage(): void
    {
        $result = $this->calculator->calculate([
            'convention' => 'w/w',
            'amount' => '900',
            'amount_unit' => 'mg',
            'total_quantity' => '100',
            'total_unit' => 'g',
        ]);

        $this->assertEqualsWithDelta(0.9, $result['percentage'], 1e-9);
    }

    public function test_converts_units_before_calculating(): void
    {
        $result = $this->calculator->calculate([
            'convention' => 'w/v',
            'amount' => '9',
            'amount_unit' => 'mg',
            'total_quantity' => '1000',
            'total_unit' => 'µL',
        ]);

        $this->assertEqualsWithDelta(0.9, $result['percentage'], 1e-9);
    }

    public function test_returns_full_percentage_when_amount_equals_total(): void
    {
        $result = $this->calculator->calculate([
            'convention' => 'v/v',
            'amount' => '50',
            'amount_unit' => 'mL',
            'total_quantity' => '50',
            'total_unit' => 'mL',
        ]);

        $this->assertSame(100.0, $result['percentage']);
    }

    public function test_rejects_unsupported_convention(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'convention' => 'mol/mol',
            'amount' => '1',
            'amount_unit' => 'g',
            'total_quantity' => '100',
            'total_unit' => 'mL',
        ]);
    }

    public function test_rejects_zero_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'convention' => 'w/v',
            'amount' => '0',
            'amount_unit' => 'g',
            'total_quantity' => '100',
            'total_unit' => 'mL',
        ]);
    }

    public function test_rejects_zero_total_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'convention' => 'w/v',
            'amount' => '1',
            'amount_unit' => 'g',
            'total_quantity' => '0',
            'total_unit' => 'mL',
        ]);
    }

    public function test_rejects_dimension_mismatch_for_wv(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'convention' => 'w/v',
            'amount' => '5',
            'amount_unit' => 'mL',
            'total_quantity' => '250',
            'total_unit' => 'mL',
        ]);
    }

    public function test_rejects_dimension_mismatch_for_ww(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'convention' => 'w/w',
            'amount' => '1',
            'amount_unit' => 'g',
            'total_quantity' => '100',
            'total_unit' => 'mL',
        ]);
    }

    public function test_rejects_unsupported_unit(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'convention' => 'w/v',
            'amount' => '1',
            'amount_unit' => 'mol',
            'total_quantity' => '100',
            'total_unit' => 'mL',
        ]);
    }

    public function test_requires_all_inputs(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'convention' => 'w/v',
            'amount' => '1',
            'amount_unit' => 'g',
            'total_quantity' => '100',
        ]);
    }

    public function test_rejects_non_numeric_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'convention' => 'w/v',
            'amount' => 'high',
            'amount_unit' => 'g',
            'total_quantity' => '100',
            'total_unit' => 'mL',
        ]);
    }
}
