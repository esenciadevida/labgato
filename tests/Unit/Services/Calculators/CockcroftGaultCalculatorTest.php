<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\CockcroftGaultCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CockcroftGaultCalculatorTest extends TestCase
{
    public function test_crcl_calculates_for_male(): void
    {
        $calculator = new CockcroftGaultCalculator;

        $result = $calculator->calculate([
            'sex' => 'male',
            'age' => 40,
            'weight' => 70,
            'serum_creatinine' => 1.0,
        ]);

        $this->assertEqualsWithDelta(97.2222, $result['crcl'], 0.0001);
    }

    public function test_crcl_calculates_for_female(): void
    {
        $calculator = new CockcroftGaultCalculator;

        $result = $calculator->calculate([
            'sex' => 'female',
            'age' => 40,
            'weight' => 60,
            'serum_creatinine' => 1.0,
        ]);

        $this->assertEqualsWithDelta(70.8333, $result['crcl'], 0.0001);
    }

    public function test_crcl_defaults_to_male(): void
    {
        $calculator = new CockcroftGaultCalculator;

        $result = $calculator->calculate([
            'age' => 40,
            'weight' => 70,
            'serum_creatinine' => 1.0,
        ]);

        $this->assertEqualsWithDelta(97.2222, $result['crcl'], 0.0001);
    }

    public function test_crcl_requires_all_inputs(): void
    {
        $calculator = new CockcroftGaultCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'male',
            'age' => 40,
            'weight' => 70,
        ]);
    }

    public function test_crcl_rejects_invalid_sex(): void
    {
        $calculator = new CockcroftGaultCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'other',
            'age' => 40,
            'weight' => 70,
            'serum_creatinine' => 1.0,
        ]);
    }

    public function test_crcl_rejects_non_positive_weight(): void
    {
        $calculator = new CockcroftGaultCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'male',
            'age' => 40,
            'weight' => 0,
            'serum_creatinine' => 1.0,
        ]);
    }

    public function test_crcl_rejects_non_positive_creatinine(): void
    {
        $calculator = new CockcroftGaultCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'male',
            'age' => 40,
            'weight' => 70,
            'serum_creatinine' => 0,
        ]);
    }

    public function test_crcl_rejects_non_numeric_inputs(): void
    {
        $calculator = new CockcroftGaultCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sex' => 'male',
            'age' => 40,
            'weight' => 70,
            'serum_creatinine' => 'high',
        ]);
    }
}
