<?php

namespace Tests\Unit\Services\Tools;

use App\Models\LaboratoryTool;
use App\Services\Tools\Engines\BufferPreparationCalculator;
use App\Services\Tools\Engines\MolarityConverter;
use App\Services\Tools\Engines\MolarityNormalityConverter;
use App\Services\Tools\Engines\NormalityConverter;
use App\Services\Tools\Engines\PercentageSolutionCalculator;
use App\Services\Tools\Engines\StockSolutionCalculator;
use App\Services\Tools\ToolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolServiceTest extends TestCase
{
    use RefreshDatabase;

    private ToolService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ToolService::class);
    }

    public function test_service_resolves_registered_percentage_solution_engine(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => PercentageSolutionCalculator::class,
        ]);

        $engine = $this->service->resolveEngine($tool);

        $this->assertInstanceOf(PercentageSolutionCalculator::class, $engine);
    }

    public function test_service_calculates_percentage_solution(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => PercentageSolutionCalculator::class,
            'input_schema' => [
                ['key' => 'convention', 'label' => 'Convention', 'type' => 'select', 'required' => true],
                ['key' => 'amount', 'label' => 'Amount of solute', 'type' => 'number', 'required' => true],
                ['key' => 'amount_unit', 'label' => 'Solute unit', 'type' => 'select', 'required' => true],
                ['key' => 'total_quantity', 'label' => 'Total solution quantity', 'type' => 'number', 'required' => true],
                ['key' => 'total_unit', 'label' => 'Solution unit', 'type' => 'select', 'required' => true],
            ],
            'output_schema' => [
                ['key' => 'percentage', 'label' => 'Percentage concentration', 'unit' => '%', 'rounding' => 'round_half_up', 'precision' => 4],
            ],
        ]);

        $result = $this->service->run($tool, [
            'convention' => 'w/v',
            'amount' => '0.9',
            'amount_unit' => 'g',
            'total_quantity' => '100',
            'total_unit' => 'mL',
        ]);

        $this->assertSame([], $result['errors']);
        $this->assertSame('0.9000', $result['outputs']['percentage']['value']);
        $this->assertSame('Percentage concentration', $result['outputs']['percentage']['label']);
        $this->assertSame('%', $result['outputs']['percentage']['unit']);
    }

    public function test_service_validates_required_percentage_solution_inputs(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => PercentageSolutionCalculator::class,
            'input_schema' => [
                ['key' => 'convention', 'label' => 'Convention', 'type' => 'select', 'required' => true],
                ['key' => 'amount', 'label' => 'Amount of solute', 'type' => 'number', 'required' => true],
                ['key' => 'amount_unit', 'label' => 'Solute unit', 'type' => 'select', 'required' => true],
                ['key' => 'total_quantity', 'label' => 'Total solution quantity', 'type' => 'number', 'required' => true],
                ['key' => 'total_unit', 'label' => 'Solution unit', 'type' => 'select', 'required' => true],
            ],
            'output_schema' => [
                ['key' => 'percentage', 'label' => 'Percentage concentration', 'unit' => '%', 'rounding' => 'round_half_up', 'precision' => 4],
            ],
        ]);

        $result = $this->service->run($tool, [
            'convention' => 'w/v',
        ]);

        $this->assertEmpty($result['outputs']);
        $this->assertArrayHasKey('amount', $result['errors']);
        $this->assertArrayHasKey('amount_unit', $result['errors']);
        $this->assertArrayHasKey('total_quantity', $result['errors']);
        $this->assertArrayHasKey('total_unit', $result['errors']);
    }

    public function test_service_rejects_dimension_mismatch(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => PercentageSolutionCalculator::class,
            'input_schema' => [
                ['key' => 'convention', 'label' => 'Convention', 'type' => 'select', 'required' => true],
                ['key' => 'amount', 'label' => 'Amount of solute', 'type' => 'number', 'required' => true],
                ['key' => 'amount_unit', 'label' => 'Solute unit', 'type' => 'select', 'required' => true],
                ['key' => 'total_quantity', 'label' => 'Total solution quantity', 'type' => 'number', 'required' => true],
                ['key' => 'total_unit', 'label' => 'Solution unit', 'type' => 'select', 'required' => true],
            ],
            'output_schema' => [
                ['key' => 'percentage', 'label' => 'Percentage concentration', 'unit' => '%', 'rounding' => 'round_half_up', 'precision' => 4],
            ],
        ]);

        $result = $this->service->run($tool, [
            'convention' => 'w/v',
            'amount' => '5',
            'amount_unit' => 'mL',
            'total_quantity' => '250',
            'total_unit' => 'mL',
        ]);

        $this->assertEmpty($result['outputs']);
        $this->assertSame('Unable to calculate a result.', $result['errors']['_calculation'][0] ?? null);
    }

    public function test_service_resolves_registered_stock_solution_engine(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => StockSolutionCalculator::class,
        ]);

        $engine = $this->service->resolveEngine($tool);

        $this->assertInstanceOf(StockSolutionCalculator::class, $engine);
    }

    public function test_service_calculates_stock_solution_mass(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => StockSolutionCalculator::class,
            'input_schema' => [
                ['key' => 'concentration', 'label' => 'Target concentration', 'type' => 'number', 'required' => true],
                ['key' => 'concentration_unit', 'label' => 'Concentration unit', 'type' => 'select', 'required' => true],
                ['key' => 'volume', 'label' => 'Final volume', 'type' => 'number', 'required' => true],
                ['key' => 'volume_unit', 'label' => 'Volume unit', 'type' => 'select', 'required' => true],
            ],
            'output_schema' => [
                ['key' => 'amount_g', 'label' => 'Amount of solute required (mass)', 'unit' => 'g', 'rounding' => 'round_half_up', 'precision' => 4],
                ['key' => 'amount_mol', 'label' => 'Amount of solute required (moles)', 'unit' => 'mol', 'rounding' => 'round_half_up', 'precision' => 4],
            ],
        ]);

        $result = $this->service->run($tool, [
            'concentration' => '9',
            'concentration_unit' => 'g/L',
            'volume' => '500',
            'volume_unit' => 'mL',
        ]);

        $this->assertSame([], $result['errors']);
        $this->assertSame('4.5000', $result['outputs']['amount_g']['value']);
        $this->assertArrayNotHasKey('amount_mol', $result['outputs']);
    }

    public function test_service_calculates_stock_solution_moles(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => StockSolutionCalculator::class,
            'input_schema' => [
                ['key' => 'concentration', 'label' => 'Target concentration', 'type' => 'number', 'required' => true],
                ['key' => 'concentration_unit', 'label' => 'Concentration unit', 'type' => 'select', 'required' => true],
                ['key' => 'volume', 'label' => 'Final volume', 'type' => 'number', 'required' => true],
                ['key' => 'volume_unit', 'label' => 'Volume unit', 'type' => 'select', 'required' => true],
            ],
            'output_schema' => [
                ['key' => 'amount_g', 'label' => 'Amount of solute required (mass)', 'unit' => 'g', 'rounding' => 'round_half_up', 'precision' => 4],
                ['key' => 'amount_mol', 'label' => 'Amount of solute required (moles)', 'unit' => 'mol', 'rounding' => 'round_half_up', 'precision' => 4],
            ],
        ]);

        $result = $this->service->run($tool, [
            'concentration' => '100',
            'concentration_unit' => 'mmol/L',
            'volume' => '250',
            'volume_unit' => 'mL',
        ]);

        $this->assertSame([], $result['errors']);
        $this->assertSame('0.0250', $result['outputs']['amount_mol']['value']);
        $this->assertArrayNotHasKey('amount_g', $result['outputs']);
    }

    public function test_service_validates_required_stock_solution_inputs(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => StockSolutionCalculator::class,
            'input_schema' => [
                ['key' => 'concentration', 'label' => 'Target concentration', 'type' => 'number', 'required' => true],
                ['key' => 'concentration_unit', 'label' => 'Concentration unit', 'type' => 'select', 'required' => true],
                ['key' => 'volume', 'label' => 'Final volume', 'type' => 'number', 'required' => true],
                ['key' => 'volume_unit', 'label' => 'Volume unit', 'type' => 'select', 'required' => true],
            ],
            'output_schema' => [
                ['key' => 'amount_g', 'label' => 'Amount of solute required (mass)', 'unit' => 'g', 'rounding' => 'round_half_up', 'precision' => 4],
                ['key' => 'amount_mol', 'label' => 'Amount of solute required (moles)', 'unit' => 'mol', 'rounding' => 'round_half_up', 'precision' => 4],
            ],
        ]);

        $result = $this->service->run($tool, [
            'concentration' => '9',
        ]);

        $this->assertEmpty($result['outputs']);
        $this->assertArrayHasKey('concentration_unit', $result['errors']);
        $this->assertArrayHasKey('volume', $result['errors']);
        $this->assertArrayHasKey('volume_unit', $result['errors']);
    }

    public function test_service_resolves_registered_buffer_preparation_engine(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => BufferPreparationCalculator::class,
        ]);

        $engine = $this->service->resolveEngine($tool);

        $this->assertInstanceOf(BufferPreparationCalculator::class, $engine);
    }

    public function test_service_calculates_buffer_preparation(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => BufferPreparationCalculator::class,
            'input_schema' => [
                ['key' => 'target_ph', 'label' => 'Target pH', 'type' => 'number', 'required' => true],
                ['key' => 'p_ka', 'label' => 'pKa', 'type' => 'number', 'required' => true],
                ['key' => 'total_concentration', 'label' => 'Total buffer concentration', 'type' => 'number', 'required' => true],
                ['key' => 'final_volume', 'label' => 'Final volume', 'type' => 'number', 'required' => true],
            ],
            'output_schema' => [
                ['key' => 'acid_concentration', 'label' => 'Acid form concentration ([HA])', 'unit' => 'mol/L', 'rounding' => 'round_half_up', 'precision' => 4],
                ['key' => 'base_concentration', 'label' => 'Base form concentration ([A⁻])', 'unit' => 'mol/L', 'rounding' => 'round_half_up', 'precision' => 4],
                ['key' => 'acid_amount', 'label' => 'Acid form (HA) required', 'unit' => 'mol', 'rounding' => 'round_half_up', 'precision' => 4],
                ['key' => 'base_amount', 'label' => 'Base form (A⁻) required', 'unit' => 'mol', 'rounding' => 'round_half_up', 'precision' => 4],
            ],
        ]);

        $result = $this->service->run($tool, [
            'target_ph' => '7.0',
            'p_ka' => '7.0',
            'total_concentration' => '0.2',
            'final_volume' => '0.5',
        ]);

        $this->assertSame([], $result['errors']);
        $this->assertSame('0.1000', $result['outputs']['acid_concentration']['value']);
        $this->assertSame('0.1000', $result['outputs']['base_concentration']['value']);
        $this->assertSame('0.0500', $result['outputs']['acid_amount']['value']);
        $this->assertSame('0.0500', $result['outputs']['base_amount']['value']);
    }

    public function test_service_resolves_registered_molarity_engine(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => MolarityConverter::class,
        ]);

        $this->assertInstanceOf(MolarityConverter::class, $this->service->resolveEngine($tool));
    }

    public function test_service_calculates_molarity_conversion(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => MolarityConverter::class,
            'input_schema' => [
                ['key' => 'value', 'label' => 'Value', 'type' => 'number', 'required' => true],
                ['key' => 'from_unit', 'label' => 'From unit', 'type' => 'select', 'required' => true],
                ['key' => 'to_unit', 'label' => 'To unit', 'type' => 'select', 'required' => true],
            ],
            'output_schema' => [
                ['key' => 'result', 'label' => 'Result', 'rounding' => 'round_half_up', 'precision' => 4],
            ],
        ]);

        $result = $this->service->run($tool, [
            'value' => '1',
            'from_unit' => 'mol/L (M)',
            'to_unit' => 'mmol/L',
        ]);

        $this->assertSame([], $result['errors']);
        $this->assertSame('1000.0000', $result['outputs']['result']['value']);
    }

    public function test_service_resolves_registered_normality_engine(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => NormalityConverter::class,
        ]);

        $this->assertInstanceOf(NormalityConverter::class, $this->service->resolveEngine($tool));
    }

    public function test_service_calculates_normality_conversion(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => NormalityConverter::class,
            'input_schema' => [
                ['key' => 'value', 'label' => 'Value', 'type' => 'number', 'required' => true],
                ['key' => 'from_unit', 'label' => 'From unit', 'type' => 'select', 'required' => true],
                ['key' => 'to_unit', 'label' => 'To unit', 'type' => 'select', 'required' => true],
            ],
            'output_schema' => [
                ['key' => 'result', 'label' => 'Result', 'rounding' => 'round_half_up', 'precision' => 4],
            ],
        ]);

        $result = $this->service->run($tool, [
            'value' => '1',
            'from_unit' => 'N',
            'to_unit' => 'meq/L',
        ]);

        $this->assertSame([], $result['errors']);
        $this->assertSame('1000.0000', $result['outputs']['result']['value']);
    }

    public function test_service_resolves_registered_molarity_normality_engine(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => MolarityNormalityConverter::class,
        ]);

        $this->assertInstanceOf(MolarityNormalityConverter::class, $this->service->resolveEngine($tool));
    }

    public function test_service_calculates_molarity_to_normality(): void
    {
        $tool = LaboratoryTool::factory()->create([
            'engine_reference' => MolarityNormalityConverter::class,
            'input_schema' => [
                ['key' => 'value', 'label' => 'Value', 'type' => 'number', 'required' => true],
                ['key' => 'from_unit', 'label' => 'From unit', 'type' => 'select', 'required' => true],
                ['key' => 'to_unit', 'label' => 'To unit', 'type' => 'select', 'required' => true],
                ['key' => 'n_factor', 'label' => 'n-factor', 'type' => 'number', 'required' => true],
            ],
            'output_schema' => [
                ['key' => 'result', 'label' => 'Result', 'rounding' => 'round_half_up', 'precision' => 4],
            ],
        ]);

        $result = $this->service->run($tool, [
            'value' => '2',
            'from_unit' => 'mol/L (M)',
            'to_unit' => 'N',
            'n_factor' => '3',
        ]);

        $this->assertSame([], $result['errors']);
        $this->assertSame('6.0000', $result['outputs']['result']['value']);
    }
}
