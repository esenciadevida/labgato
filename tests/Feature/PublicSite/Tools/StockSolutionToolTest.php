<?php

namespace Tests\Feature\PublicSite\Tools;

use App\Enums\ResourceStatus;
use App\Livewire\PublicSite\Tools\ToolRunner;
use App\Models\LaboratoryTool;
use App\Services\Tools\Engines\StockSolutionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockSolutionToolTest extends TestCase
{
    use RefreshDatabase;

    private function makeTool(): LaboratoryTool
    {
        return LaboratoryTool::factory()->create([
            'name' => 'Stock Solution Tool',
            'slug' => 'stock-solution-tool',
            'status' => ResourceStatus::PUBLISHED,
            'engine_reference' => StockSolutionCalculator::class,
            'input_schema' => [
                ['key' => 'concentration', 'label' => 'Target concentration', 'type' => 'number', 'required' => true, 'min' => 0],
                [
                    'key' => 'concentration_unit',
                    'label' => 'Concentration unit',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        ['value' => 'g/L', 'label' => 'g/L'],
                        ['value' => 'mg/mL', 'label' => 'mg/mL'],
                        ['value' => 'mol/L (M)', 'label' => 'mol/L (M)'],
                        ['value' => 'mmol/L', 'label' => 'mmol/L'],
                    ],
                ],
                ['key' => 'volume', 'label' => 'Final volume', 'type' => 'number', 'required' => true, 'min' => 0],
                [
                    'key' => 'volume_unit',
                    'label' => 'Volume unit',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        ['value' => 'mL', 'label' => 'Milliliters (mL)'],
                        ['value' => 'L', 'label' => 'Liters (L)'],
                    ],
                ],
            ],
            'output_schema' => [
                ['key' => 'amount_g', 'label' => 'Amount of solute required (mass)', 'unit' => 'g', 'rounding' => 'round_half_up', 'precision' => 2],
                ['key' => 'amount_mol', 'label' => 'Amount of solute required (moles)', 'unit' => 'mol', 'rounding' => 'round_half_up', 'precision' => 2],
            ],
        ]);
    }

    public function test_tool_detail_renders_select_fields(): void
    {
        $tool = $this->makeTool();

        $response = $this->get(route('public.tools.show', $tool->slug));

        $response->assertStatus(200);
        $response->assertSee('Target concentration');
        $response->assertSee('Concentration unit');
        $response->assertSee('Volume unit');
    }

    public function test_runner_calculates_stock_solution(): void
    {
        $tool = $this->makeTool();

        Livewire::test(ToolRunner::class, ['tool' => $tool])
            ->set('values', [
                'concentration' => '9',
                'concentration_unit' => 'g/L',
                'volume' => '500',
                'volume_unit' => 'mL',
            ])
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('validationErrors', [])
            ->assertSee('Amount of solute required (mass)')
            ->assertSee('4.50');
    }

    public function test_runner_calculates_molar_amount(): void
    {
        $tool = $this->makeTool();

        Livewire::test(ToolRunner::class, ['tool' => $tool])
            ->set('values', [
                'concentration' => '100',
                'concentration_unit' => 'mmol/L',
                'volume' => '250',
                'volume_unit' => 'mL',
            ])
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('validationErrors', [])
            ->assertSee('Amount of solute required (moles)')
            ->assertSee('0.03');
    }

    public function test_runner_rejects_missing_concentration(): void
    {
        $tool = $this->makeTool();

        Livewire::test(ToolRunner::class, ['tool' => $tool])
            ->set('values', [
                'concentration' => '',
                'concentration_unit' => 'g/L',
                'volume' => '500',
                'volume_unit' => 'mL',
            ])
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSee('Target concentration is required.');
    }
}
