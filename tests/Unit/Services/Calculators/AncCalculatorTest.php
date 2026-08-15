<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\AncCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AncCalculatorTest extends TestCase
{
    public function test_anc_calculates_correctly(): void
    {
        $calculator = new AncCalculator;

        $result = $calculator->calculate([
            'wbc' => 8,
            'neutrophils' => 50,
        ]);

        $this->assertSame(4.0, $result['anc']);
    }

    public function test_anc_includes_bands_when_provided(): void
    {
        $calculator = new AncCalculator;

        $result = $calculator->calculate([
            'wbc' => 8,
            'neutrophils' => 50,
            'bands' => 5,
        ]);

        $this->assertSame(4.4, $result['anc']);
    }

    public function test_anc_requires_core_inputs(): void
    {
        $calculator = new AncCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['wbc' => 8]);
    }

    public function test_anc_rejects_non_numeric_inputs(): void
    {
        $calculator = new AncCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'wbc' => 8,
            'neutrophils' => 'many',
        ]);
    }
}
