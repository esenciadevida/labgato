<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\MchCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MchCalculatorTest extends TestCase
{
    public function test_mch_calculates_correctly(): void
    {
        $calculator = new MchCalculator;

        $result = $calculator->calculate([
            'hemoglobin' => 14,
            'rbc' => 5,
        ]);

        $this->assertSame(28.0, $result['mch']);
    }

    public function test_mch_requires_all_inputs(): void
    {
        $calculator = new MchCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['hemoglobin' => 14]);
    }

    public function test_mch_rejects_non_numeric_inputs(): void
    {
        $calculator = new MchCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'hemoglobin' => 14,
            'rbc' => 'five',
        ]);
    }
}
