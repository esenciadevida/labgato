<?php

namespace Tests\Unit\Services\Tools;

use App\Services\Tools\Engines\BufferPreparationCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BufferPreparationCalculatorTest extends TestCase
{
    private BufferPreparationCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new BufferPreparationCalculator;
    }

    public function test_calculates_buffer_components_at_target_ph(): void
    {
        $result = $this->calculator->calculate([
            'target_ph' => '7.4',
            'p_ka' => '7.2',
            'total_concentration' => '0.1',
            'final_volume' => '1',
        ]);

        $this->assertEqualsWithDelta(1.5849, $result['ratio'], 1e-4);
        $this->assertEqualsWithDelta(0.0387, $result['acid_concentration'], 1e-4);
        $this->assertEqualsWithDelta(0.0613, $result['base_concentration'], 1e-4);
        $this->assertEqualsWithDelta(0.0387, $result['acid_amount'], 1e-4);
        $this->assertEqualsWithDelta(0.0613, $result['base_amount'], 1e-4);
    }

    public function test_equal_forms_when_ph_equals_pka(): void
    {
        $result = $this->calculator->calculate([
            'target_ph' => '7.0',
            'p_ka' => '7.0',
            'total_concentration' => '0.2',
            'final_volume' => '0.5',
        ]);

        $this->assertEqualsWithDelta(1.0, $result['ratio'], 1e-9);
        $this->assertEqualsWithDelta(0.1, $result['acid_concentration'], 1e-9);
        $this->assertEqualsWithDelta(0.1, $result['base_concentration'], 1e-9);
        $this->assertEqualsWithDelta(0.05, $result['acid_amount'], 1e-9);
        $this->assertEqualsWithDelta(0.05, $result['base_amount'], 1e-9);
    }

    public function test_mostly_acid_when_ph_below_pka(): void
    {
        $result = $this->calculator->calculate([
            'target_ph' => '5.0',
            'p_ka' => '7.2',
            'total_concentration' => '0.1',
            'final_volume' => '1',
        ]);

        $this->assertLessThan(1.0, $result['ratio']);
        $this->assertGreaterThan($result['base_concentration'], $result['acid_concentration']);
    }

    public function test_mostly_base_when_ph_above_pka(): void
    {
        $result = $this->calculator->calculate([
            'target_ph' => '9.0',
            'p_ka' => '7.2',
            'total_concentration' => '0.1',
            'final_volume' => '1',
        ]);

        $this->assertGreaterThan(1.0, $result['ratio']);
        $this->assertGreaterThan($result['acid_concentration'], $result['base_concentration']);
    }

    public function test_rejects_zero_total_concentration(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'target_ph' => '7.4',
            'p_ka' => '7.2',
            'total_concentration' => '0',
            'final_volume' => '1',
        ]);
    }

    public function test_rejects_zero_final_volume(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'target_ph' => '7.4',
            'p_ka' => '7.2',
            'total_concentration' => '0.1',
            'final_volume' => '0',
        ]);
    }

    public function test_requires_all_inputs(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'target_ph' => '7.4',
            'p_ka' => '7.2',
            'total_concentration' => '0.1',
        ]);
    }

    public function test_rejects_non_numeric_pka(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->calculate([
            'target_ph' => '7.4',
            'p_ka' => 'high',
            'total_concentration' => '0.1',
            'final_volume' => '1',
        ]);
    }
}
