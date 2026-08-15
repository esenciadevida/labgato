<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\AnionGapCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AnionGapCalculatorTest extends TestCase
{
    public function test_anion_gap_calculates_without_potassium(): void
    {
        $calculator = new AnionGapCalculator;

        $result = $calculator->calculate([
            'sodium' => 140,
            'chloride' => 104,
            'bicarbonate' => 24,
            'potassium_mode' => 'without',
            'albumin_corrected' => 'no',
        ]);

        $this->assertSame(12.0, $result['anion_gap']);
        $this->assertArrayNotHasKey('corrected_anion_gap', $result);
    }

    public function test_anion_gap_calculates_with_potassium(): void
    {
        $calculator = new AnionGapCalculator;

        $result = $calculator->calculate([
            'sodium' => 140,
            'chloride' => 104,
            'bicarbonate' => 24,
            'potassium_mode' => 'with',
            'potassium' => 4,
            'albumin_corrected' => 'no',
        ]);

        $this->assertSame(16.0, $result['anion_gap']);
    }

    public function test_anion_gap_calculates_albumin_corrected(): void
    {
        $calculator = new AnionGapCalculator;

        $result = $calculator->calculate([
            'sodium' => 140,
            'chloride' => 104,
            'bicarbonate' => 24,
            'potassium_mode' => 'without',
            'albumin_corrected' => 'yes',
            'albumin' => 3,
        ]);

        $this->assertSame(12.0, $result['anion_gap']);
        $this->assertSame(14.5, $result['corrected_anion_gap']);
    }

    public function test_anion_gap_corrected_with_potassium(): void
    {
        $calculator = new AnionGapCalculator;

        $result = $calculator->calculate([
            'sodium' => 140,
            'chloride' => 104,
            'bicarbonate' => 24,
            'potassium_mode' => 'with',
            'potassium' => 4,
            'albumin_corrected' => 'yes',
            'albumin' => 3,
        ]);

        $this->assertSame(16.0, $result['anion_gap']);
        $this->assertSame(18.5, $result['corrected_anion_gap']);
    }

    public function test_anion_gap_defaults_to_without_potassium_and_no_correction(): void
    {
        $calculator = new AnionGapCalculator;

        $result = $calculator->calculate([
            'sodium' => 140,
            'chloride' => 104,
            'bicarbonate' => 24,
        ]);

        $this->assertSame(12.0, $result['anion_gap']);
        $this->assertArrayNotHasKey('corrected_anion_gap', $result);
    }

    public function test_anion_gap_requires_all_inputs(): void
    {
        $calculator = new AnionGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sodium' => 140,
            'chloride' => 104,
        ]);
    }

    public function test_anion_gap_requires_potassium_when_mode_is_with(): void
    {
        $calculator = new AnionGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sodium' => 140,
            'chloride' => 104,
            'bicarbonate' => 24,
            'potassium_mode' => 'with',
            'albumin_corrected' => 'no',
        ]);
    }

    public function test_anion_gap_requires_albumin_when_correction_enabled(): void
    {
        $calculator = new AnionGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sodium' => 140,
            'chloride' => 104,
            'bicarbonate' => 24,
            'albumin_corrected' => 'yes',
        ]);
    }

    public function test_anion_gap_rejects_non_numeric_inputs(): void
    {
        $calculator = new AnionGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sodium' => 140,
            'chloride' => 'high',
            'bicarbonate' => 24,
        ]);
    }

    public function test_anion_gap_rejects_invalid_potassium_mode(): void
    {
        $calculator = new AnionGapCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([
            'sodium' => 140,
            'chloride' => 104,
            'bicarbonate' => 24,
            'potassium_mode' => 'sometimes',
        ]);
    }
}
