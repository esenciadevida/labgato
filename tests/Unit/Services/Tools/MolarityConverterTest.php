<?php

namespace Tests\Unit\Services\Tools;

use App\Services\Tools\Engines\MolarityConverter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MolarityConverterTest extends TestCase
{
    private MolarityConverter $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new MolarityConverter;
    }

    public function test_converts_molar_to_millimolar(): void
    {
        $result = $this->calculator->calculate([
            'value' => '1',
            'from_unit' => 'mol/L (M)',
            'to_unit' => 'mmol/L',
        ]);

        $this->assertEqualsWithDelta(1000.0, $result['result'], 1e-9);
    }

    public function test_converts_micromolar_to_nanomolar(): void
    {
        $result = $this->calculator->calculate([
            'value' => '0.5',
            'from_unit' => 'µmol/L',
            'to_unit' => 'nmol/L',
        ]);

        $this->assertEqualsWithDelta(500.0, $result['result'], 1e-9);
    }

    public function test_converts_mol_per_cubic_meter(): void
    {
        $result = $this->calculator->calculate([
            'value' => '3',
            'from_unit' => 'mol/m³',
            'to_unit' => 'mol/L (M)',
        ]);

        $this->assertEqualsWithDelta(0.003, $result['result'], 1e-9);
    }

    public function test_handles_micro_character_variant(): void
    {
        $result = $this->calculator->calculate([
            'value' => '1',
            'from_unit' => 'μmol/L',
            'to_unit' => 'mmol/L',
        ]);

        $this->assertEqualsWithDelta(0.001, $result['result'], 1e-9);
    }

    public function test_rejects_unsupported_unit(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'value' => '1',
            'from_unit' => 'g/L',
            'to_unit' => 'mmol/L',
        ]);
    }

    public function test_requires_all_inputs(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'value' => '1',
            'from_unit' => 'mol/L (M)',
        ]);
    }
}
