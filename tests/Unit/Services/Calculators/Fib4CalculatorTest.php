<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\Fib4Calculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class Fib4CalculatorTest extends TestCase
{
    public function test_fib4_calculates_correctly(): void
    {
        $calculator = new Fib4Calculator;

        $result = $calculator->calculate([
            'age' => 50,
            'ast' => 100,
            'alt' => 80,
            'platelet_count' => 150,
        ]);

        $this->assertEqualsWithDelta(3.7268, $result['fib4'], 0.0001);
    }

    public function test_fib4_low_score_excludes_advanced_fibrosis(): void
    {
        $calculator = new Fib4Calculator;

        $result = $calculator->calculate([
            'age' => 40,
            'ast' => 40,
            'alt' => 40,
            'platelet_count' => 200,
        ]);

        $this->assertEqualsWithDelta(1.2649, $result['fib4'], 0.0001);
    }

    public function test_fib4_rejects_zero_platelets(): void
    {
        $calculator = new Fib4Calculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['age' => 50, 'ast' => 100, 'alt' => 80, 'platelet_count' => 0]);
    }

    public function test_fib4_rejects_negative_alt(): void
    {
        $calculator = new Fib4Calculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['age' => 50, 'ast' => 100, 'alt' => -10, 'platelet_count' => 150]);
    }

    public function test_fib4_requires_all_inputs(): void
    {
        $calculator = new Fib4Calculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['age' => 50, 'ast' => 100, 'alt' => 80]);
    }
}
