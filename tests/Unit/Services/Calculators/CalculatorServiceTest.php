<?php

namespace Tests\Unit\Services\Calculators;

use App\Models\Calculator;
use App\Services\Calculators\CalculatorService;
use App\Services\Calculators\Engines\AnionGapCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    private CalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CalculatorService::class);
    }

    public function test_service_resolves_registered_engine(): void
    {
        $calculator = Calculator::factory()->create();

        $engine = $this->service->resolveEngine($calculator);

        $this->assertInstanceOf(AnionGapCalculator::class, $engine);
    }

    public function test_service_rejects_unregistered_engine(): void
    {
        $calculator = Calculator::factory()->create([
            'engine_reference' => 'App\\Models\\User',
        ]);

        $result = $this->service->run($calculator, [
            'sodium' => 140,
            'chloride' => 100,
            'bicarbonate' => 24,
        ]);

        $this->assertEmpty($result['outputs']);
        $this->assertArrayHasKey('_calculation', $result['errors']);
    }

    public function test_service_validates_required_inputs(): void
    {
        $calculator = Calculator::factory()->create();

        $result = $this->service->run($calculator, [
            'sodium' => 140,
            'chloride' => 100,
        ]);

        $this->assertEmpty($result['outputs']);
        $this->assertArrayHasKey('bicarbonate', $result['errors']);
    }

    public function test_service_validates_numeric_inputs(): void
    {
        $calculator = Calculator::factory()->create();

        $result = $this->service->run($calculator, [
            'sodium' => 'high',
            'chloride' => 100,
            'bicarbonate' => 24,
        ]);

        $this->assertEmpty($result['outputs']);
        $this->assertArrayHasKey('sodium', $result['errors']);
    }

    public function test_service_applies_rounding_rules(): void
    {
        $calculator = Calculator::factory()->create();

        $result = $this->service->run($calculator, [
            'sodium' => 140,
            'chloride' => 100,
            'bicarbonate' => 24.6,
        ]);

        $this->assertSame('15.4', $result['outputs']['anion_gap']['value']);
        $this->assertSame('Anion Gap', $result['outputs']['anion_gap']['label']);
        $this->assertSame('mEq/L', $result['outputs']['anion_gap']['unit']);
    }

    public function test_service_does_not_execute_arbitrary_class(): void
    {
        $calculator = Calculator::factory()->create([
            'engine_reference' => 'App\\Models\\User',
        ]);

        $result = $this->service->run($calculator, [
            'sodium' => 140,
            'chloride' => 100,
            'bicarbonate' => 24,
        ]);

        $this->assertEmpty($result['outputs']);
        $this->assertSame('Unable to calculate a result.', $result['errors']['_calculation'][0] ?? null);
    }
}
