<?php

namespace Tests\Feature\PublicSite\Tools;

use App\Enums\ResourceStatus;
use App\Livewire\PublicSite\Tools\ToolRunner;
use App\Models\LaboratoryTool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ToolDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_tool_detail_renders_for_published_tool(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'name' => 'Dilution Calculator',
            'status' => ResourceStatus::PUBLISHED,
            'formula_display' => 'C1 × V1 = C2 × V2',
        ]);

        $response = $this->get(route('public.tools.show', $tool->slug));

        $response->assertStatus(200);
        $response->assertSee('Dilution Calculator');
        $response->assertSee('C1 × V1 = C2 × V2');
    }

    public function test_tool_detail_returns_404_for_draft_tool(): void
    {
        $tool = LaboratoryTool::factory()->draft()->create(['slug' => 'hidden-tool']);

        $response = $this->get(route('public.tools.show', $tool->slug));

        $response->assertStatus(404);
    }

    public function test_tool_runner_component_renders(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'name' => 'Dilution Calculator',
            'status' => ResourceStatus::PUBLISHED,
        ]);

        $response = $this->get(route('public.tools.show', $tool->slug));

        $response->assertStatus(200);
        $response->assertSee('Calculate');
    }

    public function test_tool_runner_calculates_valid_inputs(): void
    {
        $tool = LaboratoryTool::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        Livewire::test(ToolRunner::class, ['tool' => $tool])
            ->set('values', [
                'stock_concentration' => '100',
                'final_concentration' => '50',
                'final_volume' => '10',
            ])
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('validationErrors', [])
            ->assertSee('Stock Volume (V1)');
    }

    public function test_tool_runner_reports_missing_required_inputs(): void
    {
        $tool = LaboratoryTool::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        Livewire::test(ToolRunner::class, ['tool' => $tool])
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertNotSet('validationErrors', []);
    }

    public function test_tool_detail_renders_notes_and_references_without_placeholders(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'formula_display' => '',
            'interpretation_notes' => 'Use compatible units and verify the result against the applicable laboratory procedure or manufacturer instructions.',
            'references' => json_encode([
                'Authoritative laboratory reference or manufacturer documentation applicable to the specific method.',
            ]),
        ]);

        $response = $this->get(route('public.tools.show', $tool->slug));

        $response->assertStatus(200);
        $response->assertDontSee('value x factor');
        $response->assertDontSee('>notes<');
        $response->assertDontSee('>[]');
        $response->assertSee('Use compatible units and verify the result');
        $response->assertSee('Authoritative laboratory reference');
    }

    public function test_tool_detail_hides_formula_notes_and_references_when_empty(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'formula_display' => null,
            'interpretation_notes' => null,
            'references' => '[]',
        ]);

        $response = $this->get(route('public.tools.show', $tool->slug));

        $response->assertStatus(200);
        $response->assertDontSee('value x factor');
        $response->assertDontSee('>notes<');
        $response->assertDontSee('>[]');
        $response->assertDontSee('>References</h2>');
        $response->assertDontSee('>Formula</h2>');
    }
}
