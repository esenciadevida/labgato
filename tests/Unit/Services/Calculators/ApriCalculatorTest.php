<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\ApriCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ApriCalculatorTest extends TestCase
{
    public function test_apri_calculates_correctly(): void
    {
        $calculator = new ApriCalculator;

        $result = $calculator->calculate([
            'ast' => 80,
            'platelet_count' => 150,
        ]);

        $this->assertEqualsWithDelta(1.3333, $result['apri'], 0.0001);
    }

    public function test_apri_low_score_excludes_fibrosis(): void
    {
        $calculator = new ApriCalculator;

        $result = $calculator->calculate([
            'ast' => 40,
            'platelet_count' => 200,
        ]);

        $this->assertSame(0.5, $result['apri']);
    }

    public function test_apri_high_score_suggests_cirrhosis(): void
    {
        $calculator = new ApriCalculator;

        $result = $calculator->calculate([
            'ast' => 120,
            'platelet_count' => 100,
        ]);

        $this->assertSame(3.0, $result['apri']);
    }

    public function test_apri_rejects_zero_platelets(): void
    {
        $calculator = new ApriCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['ast' => 40, 'platelet_count' => 0]);
    }

    public function test_apri_requires_all_inputs(): void
    {
        $calculator = new ApriCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['ast' => 40]);
    }
}
