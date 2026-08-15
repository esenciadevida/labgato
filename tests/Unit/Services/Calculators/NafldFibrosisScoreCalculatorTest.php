<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\NafldFibrosisScoreCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NafldFibrosisScoreCalculatorTest extends TestCase
{
    public function test_nfs_calculates_without_diabetes(): void
    {
        $calculator = new NafldFibrosisScoreCalculator;

        $result = $calculator->calculate([
            'age' => 50,
            'bmi' => 28,
            'impaired_fasting_glucose' => 'no',
            'ast' => 40,
            'alt' => 30,
            'platelet_count' => 200,
            'albumin' => 4.0,
        ]);

        $this->assertEqualsWithDelta(-1.113, $result['nafld_fibrosis_score'], 0.001);
    }

    public function test_nfs_calculates_with_diabetes(): void
    {
        $calculator = new NafldFibrosisScoreCalculator;

        $result = $calculator->calculate([
            'age' => 40,
            'bmi' => 30,
            'impaired_fasting_glucose' => 'yes',
            'ast' => 60,
            'alt' => 40,
            'platelet_count' => 100,
            'albumin' => 3.5,
        ]);

        $this->assertEqualsWithDelta(1.63, $result['nafld_fibrosis_score'], 0.001);
    }

    public function test_nfs_rejects_invalid_ifg_selection(): void
    {
        $calculator = new NafldFibrosisScoreCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'age' => 50,
            'bmi' => 28,
            'impaired_fasting_glucose' => 'maybe',
            'ast' => 40,
            'alt' => 30,
            'platelet_count' => 200,
            'albumin' => 4.0,
        ]);
    }

    public function test_nfs_rejects_zero_alt(): void
    {
        $calculator = new NafldFibrosisScoreCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'age' => 50,
            'bmi' => 28,
            'impaired_fasting_glucose' => 'no',
            'ast' => 40,
            'alt' => 0,
            'platelet_count' => 200,
            'albumin' => 4.0,
        ]);
    }

    public function test_nfs_requires_all_inputs(): void
    {
        $calculator = new NafldFibrosisScoreCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['age' => 50, 'bmi' => 28]);
    }
}
