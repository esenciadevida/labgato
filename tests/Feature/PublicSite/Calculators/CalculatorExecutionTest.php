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
            ->assertSee('Serum Sodium')
            ->assertSee('Serum Chloride')
            ->assertSee('Serum Bicarbonate')
            ->assertSee('Calculate');
    }

    public function test_valid_inputs_return_calculation_result(): void
    {
        $calculator = $this->publishedCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.sodium', 140)
            ->set('values.chloride', 100)
            ->set('values.bicarbonate', 24)
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('outputs.anion_gap.value', '16.0');
    }

    public function test_invalid_inputs_return_validation_errors(): void
    {
        $calculator = $this->publishedCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.sodium', 'not-a-number')
            ->set('values.chloride', 100)
            ->set('values.bicarbonate', 24)
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('validationErrors.sodium.0', 'Serum Sodium must be a number.');
    }

    public function test_missing_required_inputs_return_validation_errors(): void
    {
        $calculator = $this->publishedCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.sodium', 140)
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('validationErrors.chloride.0', 'Serum Chloride is required.')
            ->assertSet('validationErrors.bicarbonate.0', 'Serum Bicarbonate is required.');
    }

    public function test_calculation_result_displays_units(): void
    {
        $calculator = $this->publishedCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.sodium', 140)
            ->set('values.chloride', 100)
            ->set('values.bicarbonate', 24)
            ->call('calculate')
            ->assertSee('mEq/L')
            ->assertSet('outputs.anion_gap.unit', 'mEq/L');
    }

    public function test_calculation_failure_displays_safe_error_message(): void
    {
        $calculator = Calculator::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'engine_reference' => 'App\\Models\\User',
        ]);

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.sodium', 140)
            ->set('values.chloride', 100)
            ->set('values.bicarbonate', 24)
            ->call('calculate')
            ->assertSee('Unable to calculate a result.')
            ->assertDontSee('InvalidArgumentException');
    }
}
