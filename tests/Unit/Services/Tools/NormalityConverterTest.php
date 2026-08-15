<?php

namespace Tests\Unit\Services\Tools;

use App\Services\Tools\Engines\NormalityConverter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NormalityConverterTest extends TestCase
{
    private NormalityConverter $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new NormalityConverter;
    }

    public function test_converts_normality_to_milli_equivalents(): void
    {
        $result = $this->calculator->calculate([
            'value' => '1',
            'from_unit' => 'N',
            'to_unit' => 'meq/L',
        ]);

        $this->assertEqualsWithDelta(1000.0, $result['result'], 1e-9);
    }

    public function test_converts_equivalents_per_liter_to_normality(): void
    {
        $result = $this->calculator->calculate([
            'value' => '0.5',
            'from_unit' => 'eq/L',
            'to_unit' => 'N',
        ]);

        $this->assertEqualsWithDelta(0.5, $result['result'], 1e-9);
    }

    public function test_converts_meq_per_liter_to_eq_per_liter(): void
    {
        $result = $this->calculator->calculate([
            'value' => '250',
            'from_unit' => 'meq/L',
            'to_unit' => 'eq/L',
        ]);

        $this->assertEqualsWithDelta(0.25, $result['result'], 1e-9);
    }

    public function test_rejects_unsupported_unit(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'value' => '1',
            'from_unit' => 'mol/L',
            'to_unit' => 'N',
        ]);
    }

    public function test_requires_all_inputs(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'value' => '1',
            'from_unit' => 'N',
        ]);
    }
}
