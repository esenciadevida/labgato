<?php

namespace Tests\Feature\PublicSite\Tools;

use App\Enums\ResourceStatus;
use App\Livewire\PublicSite\Tools\ToolRunner;
use App\Models\LaboratoryTool;
use App\Services\Tools\Engines\PercentageSolutionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PercentageSolutionToolTest extends TestCase
{
    use RefreshDatabase;

    private function makeTool(): LaboratoryTool
    {
        return LaboratoryTool::factory()->create([
            'name' => 'Percentage Solution Tool',
            'slug' => 'percentage-solution-tool',
            'status' => ResourceStatus::PUBLISHED,
            'engine_reference' => PercentageSolutionCalculator::class,
            'input_schema' => [
                [
                    'key' => 'convention',
                    'label' => 'Convention',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        ['value' => 'w/v', 'label' => '% w/v (mass per volume)'],
                        ['value' => 'v/v', 'label' => '% v/v (volume per volume)'],
                        ['value' => 'w/w', 'label' => '% w/w (mass per mass)'],
                    ],
                ],
                ['key' => 'amount', 'label' => 'Amount of solute', 'type' => 'number', 'required' => true, 'min' => 0],
                [
                    'key' => 'amount_unit',
                    'label' => 'Solute unit',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        ['value' => 'g', 'label' => 'g'],
                        ['value' => 'mg', 'label' => 'mg'],
                        ['value' => 'mL', 'label' => 'mL'],
                    ],
                ],
                ['key' => 'total_quantity', 'label' => 'Total solution quantity', 'type' => 'number', 'required' => true, 'min' => 0],
                [
                    'key' => 'total_unit',
                    'label' => 'Solution unit',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        ['value' => 'mL', 'label' => 'mL'],
                        ['value' => 'L', 'label' => 'L'],
                    ],
                ],
            ],
            'output_schema' => [
                ['key' => 'percentage', 'label' => 'Percentage concentration', 'unit' => '%', 'rounding' => 'round_half_up', 'precision' => 2],
            ],
        ]);
    }

    public function test_tool_detail_renders_select_fields(): void
    {
        $tool = $this->makeTool();

        $response = $this->get(route('public.tools.show', $tool->slug));

        $response->assertStatus(200);
        $response->assertSee('Convention');
        $response->assertSee('% w/v (mass per volume)');
        $response->assertSee('Solute unit');
        $response->assertSee('Solution unit');
    }

    public function test_runner_calculates_percentage_solution(): void
    {
        $tool = $this->makeTool();

        Livewire::test(ToolRunner::class, ['tool' => $tool])
            ->set('values', [
                'convention' => 'w/v',
                'amount' => '0.9',
                'amount_unit' => 'g',
                'total_quantity' => '100',
                'total_unit' => 'mL',
            ])
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('validationErrors', [])
            ->assertSee('Percentage concentration')
            ->assertSee('0.90');
    }

    public function test_runner_rejects_missing_convention(): void
    {
        $tool = $this->makeTool();

        Livewire::test(ToolRunner::class, ['tool' => $tool])
            ->set('values', [
                'convention' => '',
                'amount' => '0.9',
                'amount_unit' => 'g',
                'total_quantity' => '100',
                'total_unit' => 'mL',
            ])
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSee('Convention is required.');
    }
}
