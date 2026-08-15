<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\AbsoluteLymphocyteCountCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AbsoluteLymphocyteCountCalculatorTest extends TestCase
{
    public function test_alc_calculates_correctly_with_normal_values(): void
    {
        $calculator = new AbsoluteLymphocyteCountCalculator;

        $result = $calculator->calculate([
            'wbc' => 8,
            'lymphocytes' => 25,
        ]);

        $this->assertSame(2.0, $result['alc']);
    }

    public function test_alc_calculates_correctly_with_decimal_values(): void
    {
        $calculator = new AbsoluteLymphocyteCountCalculator;

        $result = $calculator->calculate([
            'wbc' => 6.5,
            'lymphocytes' => 30,
        ]);

        $this->assertEqualsWithDelta(1.95, $result['alc'], 0.0001);
    }

    public function test_alc_requires_all_inputs(): void
    {
        $calculator = new AbsoluteLymphocyteCountCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['wbc' => 8]);
    }

    public function test_alc_rejects_non_numeric_inputs(): void
    {
        $calculator = new AbsoluteLymphocyteCountCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'wbc' => 'high',
            'lymphocytes' => 25,
        ]);
    }
}
