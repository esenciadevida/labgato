<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\CkdEpi2009Calculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CkdEpi2009CalculatorTest extends TestCase
{
    public function test_egfr_calculates_for_male(): void
    {
        $calculator = new CkdEpi2009Calculator;

        $result = $calculator->calculate([
            'sex' => 'male',
            'race_black' => 'no',
            'age' => 50,
            'serum_creatinine' => 1.0,
        ]);

        $this->assertEqualsWithDelta(87.3697, $result['egfr'], 0.0001);
    }

    public function test_egfr_calculates_for_female(): void
    {
        $calculator = new CkdEpi2009Calculator;

        $result = $calculator->calculate([
            'sex' => 'female',
            'race_black' => 'no',
            'age' => 40,
            'serum_creatinine' => 0.8,
        ]);

        $this->assertEqualsWithDelta(92.2199, $result['egfr'], 0.0001);
    }

    public function test_egfr_applies_black_coefficient(): void
    {
        $calculator = new CkdEpi2009Calculator;

        $result = $calculator->calculate([
            'sex' => 'male',
            'race_black' => 'yes',
            'age' => 50,
            'serum_creatinine' => 1.0,
        ]);

        $this->assertEqualsWithDelta(101.2615, $result['egfr'], 0.0001);
    }

    public function test_egfr_calculates_for_young_female_with_low_creatinine(): void
    {
        $calculator = new CkdEpi2009Calculator;

        $result = $calculator->calculate([
            'sex' => 'female',
            'race_black' => 'no',
            'age' => 25,
            'serum_creatinine' => 0.6,
        ]);

        $this->assertEqualsWithDelta(126.6845, $result['egfr'], 0.0001);
    }

    public function test_egfr_defaults_to_male_and_not_black(): void
    {
        $calculator = new CkdEpi2009Calculator;

        $result = $calculator->calculate([
            'age' => 50,
            'serum_creatinine' => 1.0,
        ]);

        $this->assertEqualsWithDelta(87.3697, $result['egfr'], 0.0001);
    }

    public function test_egfr_requires_all_inputs(): void
    {
        $calculator = new CkdEpi2009Calculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'male',
            'age' => 50,
        ]);
    }

    public function test_egfr_rejects_invalid_sex(): void
    {
        $calculator = new CkdEpi2009Calculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'other',
            'age' => 50,
            'serum_creatinine' => 1.0,
        ]);
    }

    public function test_egfr_rejects_non_positive_creatinine(): void
    {
        $calculator = new CkdEpi2009Calculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'male',
            'age' => 50,
            'serum_creatinine' => 0,
        ]);
    }

    public function test_egfr_rejects_non_numeric_inputs(): void
    {
        $calculator = new CkdEpi2009Calculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'male',
            'age' => 50,
            'serum_creatinine' => 'high',
        ]);
    }
}
