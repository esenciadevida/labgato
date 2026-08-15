<?php

namespace Tests\Feature\PublicSite;

use App\Enums\ResourceStatus;
use App\Livewire\PublicSite\Calculators\CalculatorRunner;
use App\Models\Calculator;
use App\Services\Calculators\Engines\McvCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalculatorOptionsTest extends TestCase
{
    use RefreshDatabase;

    private function mcvCalculator(): Calculator
    {
        return Calculator::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'name' => 'MCV Calculator',
            'engine' => 'php_class',
            'engine_reference' => McvCalculator::class,
            'input_schema' => [
                [
                    'key' => 'hematocrit',
                    'label' => 'Hematocrit',
                    'type' => 'number',
                    'unit' => '%',
                    'required' => true,
                ],
                [
                    'key' => 'rbc',
                    'label' => 'RBC count',
                    'type' => 'number',
                    'unit' => '10^12/L',
                    'required' => true,
                ],
                [
                    'key' => 'sex',
                    'label' => 'Sex',
                    'type' => 'select',
                    'options' => ['female', 'male'],
                    'required' => true,
                ],
            ],
            'output_schema' => [
                [
                    'key' => 'mcv',
                    'label' => 'MCV',
                    'unit' => 'fL',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                ],
            ],
        ]);
    }

    public function test_select_options_render_on_frontend(): void
    {
        $calculator = $this->mcvCalculator();

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertOk()
            ->assertSee('Sex')
            ->assertSee('value="female"', escape: false)
            ->assertSee('value="male"', escape: false)
            ->assertSee('Female')
            ->assertSee('Male');
    }

    public function test_calculator_shows_male_and_female_options(): void
    {
        $calculator = $this->mcvCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->assertSee('Female')
            ->assertSee('Male');
    }

    public function test_selecting_an_option_and_calculating_works(): void
    {
        $calculator = $this->mcvCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->set('values.rbc', 5)
            ->set('values.sex', 'female')
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('outputs.mcv.value', '84.0');
    }

    public function test_invalid_select_value_is_rejected(): void
    {
        $calculator = $this->mcvCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->set('values.rbc', 5)
            ->set('values.sex', 'not-a-valid-option')
            ->call('calculate')
            ->assertSet('validationErrors.sex.0', 'Sex contains an invalid selection.');
    }

    public function test_calculator_without_options_still_renders(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertOk()
            ->assertSee('Hematocrit')
            ->assertDontSee('Select...');
    }

    public function test_array_shaped_options_render_with_labels(): void
    {
        $calculator = Calculator::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'input_schema' => [
                [
                    'key' => 'method',
                    'label' => 'Method',
                    'type' => 'select',
                    'options' => [
                        ['value' => 'a', 'label' => 'Method A'],
                        ['value' => 'b', 'label' => 'Method B'],
                    ],
                    'required' => true,
                ],
            ],
        ]);

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertOk()
            ->assertSee('value="a"', escape: false)
            ->assertSee('Method A')
            ->assertSee('Method B');
    }
}
