<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\StrongIonDifferenceCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StrongIonDifferenceCalculatorTest extends TestCase
{
    public function test_sid_calculates_with_required_inputs_only(): void
    {
        $calculator = new StrongIonDifferenceCalculator;

        $result = $calculator->calculate([
            'sodium' => 140,
            'potassium' => 4,
            'chloride' => 102,
        ]);

        $this->assertSame(42.0, $result['sid']);
    }

    public function test_sid_calculates_with_optional_inputs(): void
    {
        $calculator = new StrongIonDifferenceCalculator;

        $result = $calculator->calculate([
            'sodium' => 140,
            'potassium' => 4,
            'chloride' => 102,
            'ionised_calcium' => 1.0,
            'magnesium' => 1.5,
            'lactate' => 1.0,
        ]);

        $this->assertSame(43.5, $result['sid']);
    }

    public function test_sid_requires_all_required_inputs(): void
    {
        $calculator = new StrongIonDifferenceCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['sodium' => 140, 'potassium' => 4]);
    }

    public function test_sid_rejects_non_numeric_optional_inputs(): void
    {
        $calculator = new StrongIonDifferenceCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sodium' => 140,
            'potassium' => 4,
            'chloride' => 102,
            'lactate' => 'many',
        ]);
    }
}
