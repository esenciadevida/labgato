<?php

namespace Tests\Feature\PublicSite\Tools;

use App\Enums\ResourceStatus;
use App\Livewire\PublicSite\Tools\ToolRunner;
use App\Models\LaboratoryTool;
use App\Services\Tools\Engines\MolarityConverter;
use App\Services\Tools\Engines\MolarityNormalityConverter;
use App\Services\Tools\Engines\NormalityConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConverterToolsTest extends TestCase
{
    use RefreshDatabase;

    private function makeConverterTool(string $engine, string $name, string $slug, array $units): LaboratoryTool
    {
        return LaboratoryTool::factory()->create([
            'name' => $name,
            'slug' => $slug,
            'status' => ResourceStatus::PUBLISHED,
            'engine_reference' => $engine,
            'input_schema' => [
                ['key' => 'value', 'label' => 'Value', 'type' => 'number', 'required' => true],
                [
                    'key' => 'from_unit',
                    'label' => 'From unit',
                    'type' => 'select',
                    'required' => true,
                    'options' => array_map(static fn (string $u) => ['value' => $u, 'label' => $u], $units),
                ],
                [
                    'key' => 'to_unit',
                    'label' => 'To unit',
                    'type' => 'select',
                    'required' => true,
                    'options' => array_map(static fn (string $u) => ['value' => $u, 'label' => $u], $units),
                ],
            ],
            'output_schema' => [
                ['key' => 'result', 'label' => 'Result', 'rounding' => 'round_half_up', 'precision' => 4],
            ],
        ]);
    }

    public function test_molarity_converter_calculates(): void
    {
        $tool = $this->makeConverterTool(
            MolarityConverter::class,
            'Molarity Converter',
            'molarity-converter',
            ['mol/L (M)', 'mmol/L', 'µmol/L', 'nmol/L', 'mol/m³']
        );

        Livewire::test(ToolRunner::class, ['tool' => $tool])
            ->set('values', ['value' => '1', 'from_unit' => 'mol/L (M)', 'to_unit' => 'mmol/L'])
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('validationErrors', [])
            ->assertSee('1000.0000');
    }

    public function test_normality_converter_calculates(): void
    {
        $tool = $this->makeConverterTool(
            NormalityConverter::class,
            'Normality Converter',
            'normality-converter',
            ['N', 'meq/L', 'eq/L']
        );

        Livewire::test(ToolRunner::class, ['tool' => $tool])
            ->set('values', ['value' => '1', 'from_unit' => 'N', 'to_unit' => 'meq/L'])
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('validationErrors', [])
            ->assertSee('1000.0000');
    }

    public function test_molarity_normality_converter_calculates_with_n_factor(): void
    {
        $tool = $this->makeConverterTool(
            MolarityNormalityConverter::class,
            'Molarity ↔ Normality Converter',
            'molarity--to--normality-converter',
            ['mol/L (M)', 'N', 'mmol/L', 'meq/L']
        );

        $tool->update([
            'input_schema' => array_merge($tool->input_schema, [
                ['key' => 'n_factor', 'label' => 'n-factor', 'type' => 'number', 'required' => true],
            ]),
        ]);

        Livewire::test(ToolRunner::class, ['tool' => $tool])
            ->set('values', ['value' => '2', 'from_unit' => 'mol/L (M)', 'to_unit' => 'N', 'n_factor' => '3'])
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('validationErrors', [])
            ->assertSee('6.0000');
    }

    public function test_converters_render_from_unit_select(): void
    {
        $tool = $this->makeConverterTool(
            MolarityConverter::class,
            'Molarity Converter',
            'molarity-converter',
            ['mol/L (M)', 'mmol/L', 'µmol/L', 'nmol/L', 'mol/m³']
        );

        $response = $this->get(route('public.tools.show', $tool->slug));

        $response->assertStatus(200);
        $response->assertSee('From unit');
        $response->assertSee('To unit');
        $response->assertSee('µmol/L');
    }
}
