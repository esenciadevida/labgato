<?php

namespace Tests\Feature\PublicSite\Tools;

use App\Enums\ResourceStatus;
use App\Livewire\PublicSite\Tools\ToolRunner;
use App\Models\LaboratoryTool;
use App\Services\Tools\Engines\BufferPreparationCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BufferPreparationToolTest extends TestCase
{
    use RefreshDatabase;

    private function makeTool(): LaboratoryTool
    {
        return LaboratoryTool::factory()->create([
            'name' => 'Buffer Preparation Tool',
            'slug' => 'buffer-preparation-tool',
            'status' => ResourceStatus::PUBLISHED,
            'engine_reference' => BufferPreparationCalculator::class,
            'input_schema' => [
                ['key' => 'target_ph', 'label' => 'Target pH', 'type' => 'number', 'min' => 0, 'max' => 14, 'step' => 0.01, 'required' => true],
                ['key' => 'p_ka', 'label' => 'pKa', 'type' => 'number', 'required' => true],
                ['key' => 'total_concentration', 'label' => 'Total buffer concentration', 'type' => 'number', 'min' => 0, 'required' => true],
                ['key' => 'final_volume', 'label' => 'Final volume', 'type' => 'number', 'min' => 0, 'required' => true],
            ],
            'output_schema' => [
                ['key' => 'ratio', 'label' => 'Base/Acid ratio ([A⁻]/[HA])', 'rounding' => 'round_half_up', 'precision' => 2],
                ['key' => 'acid_concentration', 'label' => 'Acid form concentration ([HA])', 'unit' => 'mol/L', 'rounding' => 'round_half_up', 'precision' => 4],
                ['key' => 'base_concentration', 'label' => 'Base form concentration ([A⁻])', 'unit' => 'mol/L', 'rounding' => 'round_half_up', 'precision' => 4],
                ['key' => 'acid_amount', 'label' => 'Acid form (HA) required', 'unit' => 'mol', 'rounding' => 'round_half_up', 'precision' => 4],
                ['key' => 'base_amount', 'label' => 'Base form (A⁻) required', 'unit' => 'mol', 'rounding' => 'round_half_up', 'precision' => 4],
            ],
        ]);
    }

    public function test_tool_detail_renders_buffer_fields(): void
    {
        $tool = $this->makeTool();

        $response = $this->get(route('public.tools.show', $tool->slug));

        $response->assertStatus(200);
        $response->assertSee('Target pH');
        $response->assertSee('pKa');
        $response->assertSee('Total buffer concentration');
        $response->assertSee('Final volume');
    }

    public function test_runner_calculates_buffer_components(): void
    {
        $tool = $this->makeTool();

        Livewire::test(ToolRunner::class, ['tool' => $tool])
            ->set('values', [
                'target_ph' => '7.0',
                'p_ka' => '7.0',
                'total_concentration' => '0.2',
                'final_volume' => '0.5',
            ])
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('validationErrors', [])
            ->assertSee('Acid form concentration ([HA])')
            ->assertSee('0.1000');
    }

    public function test_runner_rejects_missing_required_inputs(): void
    {
        $tool = $this->makeTool();

        Livewire::test(ToolRunner::class, ['tool' => $tool])
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSee('Target pH is required.');
    }
}
