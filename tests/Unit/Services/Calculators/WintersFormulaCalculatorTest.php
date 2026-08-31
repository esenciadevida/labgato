<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\WintersFormulaCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class WintersFormulaCalculatorTest extends TestCase
{
    public function test_winters_formula_calculates_anticipated_paco2(): void
    {
        $calculator = new WintersFormulaCalculator;

        $result = $calculator->calculate(['bicarbonate' => 12]);

        $this->assertSame(26.0, $result['anticipated_paco2']);
        $this->assertSame(24.0, $result['paco2_lower']);
        $this->assertSame(28.0, $result['paco2_upper']);
    }

    public function test_winters_formula_accommodates_higher_bicarbonate(): void
    {
        $calculator = new WintersFormulaCalculator;

        $result = $calculator->calculate(['bicarbonate' => 20]);

        $this->assertSame(38.0, $result['anticipated_paco2']);
    }

    public function test_winters_formula_requires_bicarbonate(): void
    {
        $calculator = new WintersFormulaCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([]);
    }

    public function test_winters_formula_rejects_non_numeric_input(): void
    {
        $calculator = new WintersFormulaCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['bicarbonate' => 'low']);
    }
}