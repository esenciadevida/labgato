<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\GreenKingIndexCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GreenKingIndexCalculatorTest extends TestCase
{
    public function test_green_king_index_calculates_correctly(): void
    {
        $calculator = new GreenKingIndexCalculator;

        $result = $calculator->calculate([
            'mcv' => 60,
            'rdw' => 15,
            'hemoglobin' => 10,
        ]);

        $this->assertSame(54.0, $result['green_king_index']);
    }

    public function test_green_king_index_calculates_correctly_with_decimal_values(): void
    {
        $calculator = new GreenKingIndexCalculator;

        $result = $calculator->calculate([
            'mcv' => 65.3,
            'rdw' => 16.2,
            'hemoglobin' => 12.5,
        ]);

        $this->assertEqualsWithDelta(55.2626, $result['green_king_index'], 0.0001);
    }

    public function test_green_king_index_requires_all_inputs(): void
    {
        $calculator = new GreenKingIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'mcv' => 60,
            'rdw' => 15,
        ]);
    }

    public function test_green_king_index_rejects_non_numeric_inputs(): void
    {
        $calculator = new GreenKingIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'mcv' => 60,
            'rdw' => 15,
            'hemoglobin' => 'low',
        ]);
    }
}
