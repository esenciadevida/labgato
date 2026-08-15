<?php

namespace Tests\Feature\PublicSite\Calculators;

use App\Enums\ResourceStatus;
use App\Livewire\PublicSite\Calculators\CalculatorRunner;
use App\Models\Calculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalculatorExecutionTest extends TestCase
{
    use RefreshDatabase;

    private function publishedCalculator(): Calculator
    {
        return Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
    }

    public function test_calculator_runner_renders_inputs_from_schema(): void
    {
        $calculator = $this->publishedCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->assertSee('Hematocrit')
            ->assertSee('RBC count')
            ->assertSee('Calculate');
    }

    public function test_valid_inputs_return_calculation_result(): void
    {
        $calculator = $this->publishedCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->set('values.rbc', 5)
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('outputs.mcv.value', '84.0');
    }

    public function test_invalid_inputs_return_validation_errors(): void
    {
        $calculator = $this->publishedCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 'not-a-number')
            ->set('values.rbc', 5)
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('validationErrors.hematocrit.0', 'Hematocrit must be a number.');
    }

    public function test_missing_required_inputs_return_validation_errors(): void
    {
        $calculator = $this->publishedCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('validationErrors.rbc.0', 'RBC count is required.');
    }

    public function test_calculation_result_displays_units(): void
    {
        $calculator = $this->publishedCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->set('values.rbc', 5)
            ->call('calculate')
            ->assertSee('fL')
            ->assertSet('outputs.mcv.unit', 'fL');
    }

    public function test_calculation_failure_displays_safe_error_message(): void
    {
        $calculator = Calculator::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'engine_reference' => 'App\\Models\\User',
        ]);

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->set('values.rbc', 5)
            ->call('calculate')
            ->assertSee('Unable to calculate a result.')
            ->assertDontSee('InvalidArgumentException');
    }
}
