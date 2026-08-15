<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\CkdEpi2021Calculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CkdEpi2021CalculatorTest extends TestCase
{
    public function test_egfr_calculates_for_male(): void
    {
        $calculator = new CkdEpi2021Calculator;

        $result = $calculator->calculate([
            'sex' => 'male',
            'age' => 50,
            'serum_creatinine' => 1.0,
        ]);

        $this->assertEqualsWithDelta(91.6915, $result['egfr'], 0.0001);
    }

    public function test_egfr_calculates_for_female(): void
    {
        $calculator = new CkdEpi2021Calculator;

        $result = $calculator->calculate([
            'sex' => 'female',
            'age' => 40,
            'serum_creatinine' => 0.8,
        ]);

        $this->assertEqualsWithDelta(95.4637, $result['egfr'], 0.0001);
    }

    public function test_egfr_calculates_for_young_female_with_low_creatinine(): void
    {
        $calculator = new CkdEpi2021Calculator;

        $result = $calculator->calculate([
            'sex' => 'female',
            'age' => 25,
            'serum_creatinine' => 0.6,
        ]);

        $this->assertEqualsWithDelta(127.6668, $result['egfr'], 0.0001);
    }

    public function test_egfr_calculates_for_elderly_male_with_high_creatinine(): void
    {
        $calculator = new CkdEpi2021Calculator;

        $result = $calculator->calculate([
            'sex' => 'male',
            'age' => 80,
            'serum_creatinine' => 2.0,
        ]);

        $this->assertEqualsWithDelta(33.1179, $result['egfr'], 0.0001);
    }

    public function test_egfr_defaults_to_male(): void
    {
        $calculator = new CkdEpi2021Calculator;

        $result = $calculator->calculate([
            'age' => 50,
            'serum_creatinine' => 1.0,
        ]);

        $this->assertEqualsWithDelta(91.6915, $result['egfr'], 0.0001);
    }

    public function test_egfr_requires_all_inputs(): void
    {
        $calculator = new CkdEpi2021Calculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'male',
            'age' => 50,
        ]);
    }

    public function test_egfr_rejects_invalid_sex(): void
    {
        $calculator = new CkdEpi2021Calculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'other',
            'age' => 50,
            'serum_creatinine' => 1.0,
        ]);
    }

    public function test_egfr_rejects_non_positive_creatinine(): void
    {
        $calculator = new CkdEpi2021Calculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'male',
            'age' => 50,
            'serum_creatinine' => 0,
        ]);
    }

    public function test_egfr_rejects_non_positive_age(): void
    {
        $calculator = new CkdEpi2021Calculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'male',
            'age' => 0,
            'serum_creatinine' => 1.0,
        ]);
    }

    public function test_egfr_rejects_non_numeric_inputs(): void
    {
        $calculator = new CkdEpi2021Calculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'male',
            'age' => 50,
            'serum_creatinine' => 'high',
        ]);
    }
}
