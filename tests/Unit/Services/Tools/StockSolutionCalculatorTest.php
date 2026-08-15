<?php

namespace Tests\Unit\Services\Tools;

use App\Services\Tools\Engines\StockSolutionCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StockSolutionCalculatorTest extends TestCase
{
    private StockSolutionCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new StockSolutionCalculator;
    }

    public function test_calculates_mass_amount_for_mass_concentration(): void
    {
        $result = $this->calculator->calculate([
            'concentration' => '9',
            'concentration_unit' => 'g/L',
            'volume' => '500',
            'volume_unit' => 'mL',
        ]);

        $this->assertEqualsWithDelta(4.5, $result['amount_g'], 1e-9);
        $this->assertArrayNotHasKey('amount_mol', $result);
    }

    public function test_calculates_molar_amount_for_molar_concentration(): void
    {
        $result = $this->calculator->calculate([
            'concentration' => '100',
            'concentration_unit' => 'mmol/L',
            'volume' => '250',
            'volume_unit' => 'mL',
        ]);

        $this->assertEqualsWithDelta(0.025, $result['amount_mol'], 1e-9);
        $this->assertArrayNotHasKey('amount_g', $result);
    }

    public function test_converts_volume_units(): void
    {
        $result = $this->calculator->calculate([
            'concentration' => '1',
            'concentration_unit' => 'mg/mL',
            'volume' => '2',
            'volume_unit' => 'L',
        ]);

        $this->assertEqualsWithDelta(2.0, $result['amount_g'], 1e-9);
    }

    public function test_handles_microliters(): void
    {
        $result = $this->calculator->calculate([
            'concentration' => '50',
            'concentration_unit' => 'µmol/L',
            'volume' => '200',
            'volume_unit' => 'µL',
        ]);

        $this->assertEqualsWithDelta(1e-8, $result['amount_mol'], 1e-12);
    }

    public function test_handles_deciliters(): void
    {
        $result = $this->calculator->calculate([
            'concentration' => '10',
            'concentration_unit' => 'g/dL',
            'volume' => '3',
            'volume_unit' => 'dL',
        ]);

        $this->assertEqualsWithDelta(30.0, $result['amount_g'], 1e-9);
    }

    public function test_rejects_zero_concentration(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'concentration' => '0',
            'concentration_unit' => 'g/L',
            'volume' => '500',
            'volume_unit' => 'mL',
        ]);
    }

    public function test_rejects_zero_volume(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'concentration' => '9',
            'concentration_unit' => 'g/L',
            'volume' => '0',
            'volume_unit' => 'mL',
        ]);
    }

    public function test_rejects_unsupported_concentration_unit(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'concentration' => '9',
            'concentration_unit' => 'mol/m³',
            'volume' => '500',
            'volume_unit' => 'mL',
        ]);
    }

    public function test_rejects_unsupported_volume_unit(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'concentration' => '9',
            'concentration_unit' => 'g/L',
            'volume' => '500',
            'volume_unit' => 'gallon',
        ]);
    }

    public function test_requires_all_inputs(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'concentration' => '9',
            'concentration_unit' => 'g/L',
            'volume' => '500',
        ]);
    }

    public function test_rejects_non_numeric_concentration(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'concentration' => 'high',
            'concentration_unit' => 'g/L',
            'volume' => '500',
            'volume_unit' => 'mL',
        ]);
    }
}
