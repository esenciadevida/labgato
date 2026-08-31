<?php

namespace Tests\Unit\Services\Calculators;

use App\Services\Calculators\Engines\SmearPlateletEstimateCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SmearPlateletEstimateCalculatorTest extends TestCase
{
    public function test_smear_estimate_uses_20000_factor(): void
    {
        $calculator = new SmearPlateletEstimateCalculator;

        $result = $calculator->calculate(['platelets_per_field' => 10]);

        $this->assertSame(200000.0, $result['estimated_platelets']);
    }

    public function test_smear_estimate_normal_range(): void
    {
        $calculator = new SmearPlateletEstimateCalculator;

        $result = $calculator->calculate(['platelets_per_field' => 15]);

        $this->assertSame(300000.0, $result['estimated_platelets']);
    }

    public function test_smear_estimate_thrombocytopenic(): void
    {
        $calculator = new SmearPlateletEstimateCalculator;

        $result = $calculator->calculate(['platelets_per_field' => 3]);

        $this->assertSame(60000.0, $result['estimated_platelets']);
    }

    public function test_smear_estimate_zero_fields(): void
    {
        $calculator = new SmearPlateletEstimateCalculator;

        $result = $calculator->calculate(['platelets_per_field' => 0]);

        $this->assertSame(0.0, $result['estimated_platelets']);
    }

    public function test_smear_estimate_requires_input(): void
    {
        $calculator = new SmearPlateletEstimateCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate([]);
    }

    public function test_smear_estimate_rejects_non_numeric(): void
    {
        $calculator = new SmearPlateletEstimateCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['platelets_per_field' => 'ten']);
    }

    public function test_smear_estimate_rejects_negative_count(): void
    {
        $calculator = new SmearPlateletEstimateCalculator;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculate(['platelets_per_field' => -5]);
    }
}