<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\MeldThreeCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MeldThreeCalculatorTest extends TestCase
{
    public function test_meld_male_baseline_scores_6(): void
    {
        $calculator = new MeldThreeCalculator;

        $result = $calculator->calculate([
            'creatinine' => 1,
            'bilirubin' => 1,
            'inr' => 1,
            'sodium' => 137,
            'albumin' => 3.5,
            'sex' => 'male',
            'dialysis' => 'no',
        ]);

        $this->assertSame(6.0, $result['meld_3']);
    }

    public function test_meld_female_baseline_scores_7(): void
    {
        $calculator = new MeldThreeCalculator;

        $result = $calculator->calculate([
            'creatinine' => 1,
            'bilirubin' => 1,
            'inr' => 1,
            'sodium' => 137,
            'albumin' => 3.5,
            'sex' => 'female',
            'dialysis' => 'no',
        ]);

        $this->assertSame(7.0, $result['meld_3']);
    }

    public function test_meld_is_bounded_6_to_40(): void
    {
        $calculator = new MeldThreeCalculator;

        $result = $calculator->calculate([
            'creatinine' => 15,
            'bilirubin' => 40,
            'inr' => 15,
            'sodium' => 100,
            'albumin' => 0.5,
            'sex' => 'female',
            'dialysis' => 'yes',
        ]);

        $this->assertGreaterThanOrEqual(6.0, $result['meld_3']);
        $this->assertLessThanOrEqual(40.0, $result['meld_3']);
    }

    public function test_meld_dialysis_forces_creatinine_3(): void
    {
        $calculator = new MeldThreeCalculator;

        $withDialysis = $calculator->calculate([
            'creatinine' => 1.0,
            'bilirubin' => 5,
            'inr' => 2,
            'sodium' => 130,
            'albumin' => 2.5,
            'sex' => 'male',
            'dialysis' => 'yes',
        ]);

        $withoutDialysis = $calculator->calculate([
            'creatinine' => 1.0,
            'bilirubin' => 5,
            'inr' => 2,
            'sodium' => 130,
            'albumin' => 2.5,
            'sex' => 'male',
            'dialysis' => 'no',
        ]);

        $this->assertGreaterThan($withoutDialysis['meld_3'], $withDialysis['meld_3']);
    }

    public function test_meld_rejects_invalid_sex(): void
    {
        $calculator = new MeldThreeCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'creatinine' => 1,
            'bilirubin' => 1,
            'inr' => 1,
            'sodium' => 137,
            'albumin' => 3.5,
            'sex' => 'other',
            'dialysis' => 'no',
        ]);
    }

    public function test_meld_requires_all_inputs(): void
    {
        $calculator = new MeldThreeCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'creatinine' => 1,
            'bilirubin' => 1,
            'inr' => 1,
            'sodium' => 137,
        ]);
    }
}