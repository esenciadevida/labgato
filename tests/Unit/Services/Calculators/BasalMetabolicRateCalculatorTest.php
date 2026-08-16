<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\BasalMetabolicRateCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BasalMetabolicRateCalculatorTest extends TestCase
{
    public function test_bmr_male_calculates_correctly(): void
    {
        $calculator = new BasalMetabolicRateCalculator;

        $result = $calculator->calculate(['weight' => 80, 'height' => 180, 'age' => 30, 'sex' => 'male']);

        $this->assertEqualsWithDelta(1780, $result['bmr'], 0.001);
    }

    public function test_bmr_female_calculates_correctly(): void
    {
        $calculator = new BasalMetabolicRateCalculator;

        $result = $calculator->calculate(['weight' => 80, 'height' => 180, 'age' => 30, 'sex' => 'female']);

        $this->assertEqualsWithDelta(1614, $result['bmr'], 0.001);
    }

    public function test_rejects_zero_age(): void
    {
        $calculator = new BasalMetabolicRateCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['weight' => 80, 'height' => 180, 'age' => 0, 'sex' => 'male']);
    }

    public function test_missing_sex_defaults_to_male(): void
    {
        $calculator = new BasalMetabolicRateCalculator;

        $result = $calculator->calculate(['weight' => 80, 'height' => 180, 'age' => 30]);

        $this->assertEqualsWithDelta(1780, $result['bmr'], 0.001);
    }

    public function test_requires_all_numeric_inputs(): void
    {
        $calculator = new BasalMetabolicRateCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['weight' => 80, 'height' => 180, 'sex' => 'female']);
    }
}
