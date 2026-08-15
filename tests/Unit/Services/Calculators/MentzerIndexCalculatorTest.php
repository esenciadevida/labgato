<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\MentzerIndexCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MentzerIndexCalculatorTest extends TestCase
{
    public function test_mentzer_index_calculates_correctly(): void
    {
        $calculator = new MentzerIndexCalculator;

        $result = $calculator->calculate([
            'mcv' => 60,
            'rbc' => 5,
        ]);

        $this->assertSame(12.0, $result['mentzer_index']);
    }

    public function test_mentzer_index_above_thirteen_suggests_ida(): void
    {
        $calculator = new MentzerIndexCalculator;

        $result = $calculator->calculate([
            'mcv' => 70,
            'rbc' => 4.5,
        ]);

        $this->assertEqualsWithDelta(15.5556, $result['mentzer_index'], 0.0001);
    }

    public function test_mentzer_index_requires_all_inputs(): void
    {
        $calculator = new MentzerIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['mcv' => 60]);
    }

    public function test_mentzer_index_rejects_non_numeric_inputs(): void
    {
        $calculator = new MentzerIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'mcv' => 60,
            'rbc' => 'many',
        ]);
    }
}
