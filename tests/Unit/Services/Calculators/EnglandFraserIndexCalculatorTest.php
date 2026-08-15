<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\EnglandFraserIndexCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EnglandFraserIndexCalculatorTest extends TestCase
{
    public function test_england_fraser_index_calculates_correctly(): void
    {
        $calculator = new EnglandFraserIndexCalculator;

        $result = $calculator->calculate([
            'mcv' => 75.4,
            'rbc' => 4.5,
            'hemoglobin' => 12,
        ]);

        $this->assertEqualsWithDelta(7.5, $result['england_fraser_index'], 0.0001);
    }

    public function test_england_fraser_index_is_negative_in_thalassemia_pattern(): void
    {
        $calculator = new EnglandFraserIndexCalculator;

        $result = $calculator->calculate([
            'mcv' => 60,
            'rbc' => 5,
            'hemoglobin' => 12,
        ]);

        $this->assertEqualsWithDelta(-8.4, $result['england_fraser_index'], 0.0001);
    }

    public function test_england_fraser_index_requires_all_inputs(): void
    {
        $calculator = new EnglandFraserIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'mcv' => 60,
            'rbc' => 5,
        ]);
    }

    public function test_england_fraser_index_rejects_non_numeric_inputs(): void
    {
        $calculator = new EnglandFraserIndexCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'mcv' => 60,
            'rbc' => 5,
            'hemoglobin' => 'low',
        ]);
    }
}
