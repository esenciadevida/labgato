<?php

namespace Tests\Unit\Services\Tools;

use App\Services\Tools\Engines\MolarityNormalityConverter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MolarityNormalityConverterTest extends TestCase
{
    private MolarityNormalityConverter $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new MolarityNormalityConverter;
    }

    public function test_converts_molarity_to_normality(): void
    {
        $result = $this->calculator->calculate([
            'value' => '2',
            'from_unit' => 'mol/L (M)',
            'to_unit' => 'N',
            'n_factor' => '3',
        ]);

        $this->assertEqualsWithDelta(6.0, $result['result'], 1e-9);
    }

    public function test_converts_normality_to_molarity(): void
    {
        $result = $this->calculator->calculate([
            'value' => '6',
            'from_unit' => 'N',
            'to_unit' => 'mol/L (M)',
            'n_factor' => '3',
        ]);

        $this->assertEqualsWithDelta(2.0, $result['result'], 1e-9);
    }

    public function test_converts_molarity_with_unit_conversion(): void
    {
        $result = $this->calculator->calculate([
            'value' => '500',
            'from_unit' => 'mmol/L',
            'to_unit' => 'N',
            'n_factor' => '2',
        ]);

        $this->assertEqualsWithDelta(1.0, $result['result'], 1e-9);
    }

    public function test_converts_same_dimension_without_n_factor(): void
    {
        $result = $this->calculator->calculate([
            'value' => '1',
            'from_unit' => 'mol/L (M)',
            'to_unit' => 'mmol/L',
        ]);

        $this->assertEqualsWithDelta(1000.0, $result['result'], 1e-9);
    }

    public function test_rejects_zero_n_factor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'value' => '2',
            'from_unit' => 'mol/L (M)',
            'to_unit' => 'N',
            'n_factor' => '0',
        ]);
    }

    public function test_rejects_missing_n_factor_for_cross_dimension(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'value' => '2',
            'from_unit' => 'mol/L (M)',
            'to_unit' => 'N',
        ]);
    }

    public function test_rejects_non_numeric_n_factor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'value' => '2',
            'from_unit' => 'mol/L (M)',
            'to_unit' => 'N',
            'n_factor' => 'abc',
        ]);
    }

    public function test_rejects_unsupported_unit(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'value' => '2',
            'from_unit' => 'mol/L (M)',
            'to_unit' => 'g/L',
            'n_factor' => '3',
        ]);
    }

    public function test_requires_all_inputs(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'value' => '2',
            'from_unit' => 'mol/L (M)',
        ]);
    }
}
