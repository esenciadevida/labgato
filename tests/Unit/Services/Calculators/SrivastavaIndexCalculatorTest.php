<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\SrivastavaIndexCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SrivastavaIndexCalculatorTest extends TestCase
{
    public function test_srivastava_index_calculates_correctly(): void
    {
        $calculator = new SrivastavaIndexCalculator;

        $result = $calculator->calculate([
            'mch' => 22,
            'rbc' => 5,
        ]);

        $this->assertEqualsWithDelta(4.4, $result['srivastava_index'], 0.0001);
    }

    public function test_srivastava_index_below_3_8_suggests_thalassemia(): void
    {
        $calculator = new SrivastavaIndexCalculator;

        $result = $calculator->calculate([
            'mch' => 19,
            'rbc' => 5.2,
        ]);

        $this->assertEqualsWithDelta(3.6538, $result['srivastava_index'], 0.0001);
    }

    public function test_srivastava_index_requires_all_inputs(): void
    {
        $calculator = new SrivastavaIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['mch' => 22]);
    }

    public function test_srivastava_index_rejects_non_numeric_inputs(): void
    {
        $calculator = new SrivastavaIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'mch' => 22,
            'rbc' => 'many',
        ]);
    }
}
