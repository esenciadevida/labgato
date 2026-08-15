<?php

namespace Tests\Unit\Services\Calculators;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\ReferenceRange;
use App\Services\Calculators\CalculatorService;
use App\Services\Calculators\Engines\AnionGapCalculator;
use App\Services\Calculators\Engines\BilirubinRatioCalculator;
use App\Services\Calculators\Engines\CkdEpi2021Calculator;
use App\Services\Calculators\Engines\Fib4Calculator;
use App\Services\Calculators\Engines\GlobulinCalculator;
use App\Services\Calculators\Engines\McvCalculator;
use App\Services\Calculators\Engines\NafldFibrosisScoreCalculator;
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

        $this->assertInstanceOf(McvCalculator::class, $engine);
    }

    public function test_service_rejects_unregistered_engine(): void
    {
        $calculator = Calculator::factory()->create([
            'engine_reference' => 'App\\Models\\User',
        ]);

        $result = $this->service->run($calculator, [
            'hematocrit' => 42,
            'rbc' => 5,
        ]);

        $this->assertEmpty($result['outputs']);
        $this->assertArrayHasKey('_calculation', $result['errors']);
    }

    public function test_service_validates_required_inputs(): void
    {
        $calculator = Calculator::factory()->create();

        $result = $this->service->run($calculator, [
            'hematocrit' => 42,
        ]);

        $this->assertEmpty($result['outputs']);
        $this->assertArrayHasKey('rbc', $result['errors']);
    }

    public function test_service_validates_numeric_inputs(): void
    {
        $calculator = Calculator::factory()->create();

        $result = $this->service->run($calculator, [
            'hematocrit' => 'high',
            'rbc' => 5,
        ]);

        $this->assertEmpty($result['outputs']);
        $this->assertArrayHasKey('hematocrit', $result['errors']);
    }

    public function test_service_applies_rounding_rules(): void
    {
        $calculator = Calculator::factory()->create();

        $result = $this->service->run($calculator, [
            'hematocrit' => 42,
            'rbc' => 5,
        ]);

        $this->assertSame('84.0', $result['outputs']['mcv']['value']);
        $this->assertSame('MCV', $result['outputs']['mcv']['label']);
        $this->assertSame('fL', $result['outputs']['mcv']['unit']);
    }

    public function test_service_does_not_execute_arbitrary_class(): void
    {
        $calculator = Calculator::factory()->create([
            'engine_reference' => 'App\\Models\\User',
        ]);

        $result = $this->service->run($calculator, [
            'hematocrit' => 42,
            'rbc' => 5,
        ]);

        $this->assertEmpty($result['outputs']);
        $this->assertSame('Unable to calculate a result.', $result['errors']['_calculation'][0] ?? null);
    }

    public function test_output_attaches_reference_range_when_reference_key_matches(): void
    {
        ReferenceRange::create([
            'name' => 'MCV - Adult',
            'slug' => 'mcv-adult',
            'analyte_name' => 'MCV',
            'parameter_key' => 'mcv',
            'sex' => 'any',
            'unit' => 'fL',
            'lower_limit' => 80,
            'upper_limit' => 100,
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $calculator = Calculator::factory()->create([
            'output_schema' => [
                [
                    'key' => 'mcv',
                    'label' => 'MCV',
                    'unit' => 'fL',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                    'reference_key' => 'mcv',
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'hematocrit' => 42,
            'rbc' => 5,
        ]);

        $this->assertSame('80–100 fL', $result['outputs']['mcv']['reference']['display']);
        $this->assertSame('normal', $result['outputs']['mcv']['reference']['flag']);
        $this->assertStringContainsString('/reference-ranges/mcv-adult', $result['outputs']['mcv']['reference']['url']);
    }

    public function test_output_reference_is_null_when_no_range_exists(): void
    {
        $calculator = Calculator::factory()->create([
            'output_schema' => [
                [
                    'key' => 'mcv',
                    'label' => 'MCV',
                    'unit' => 'fL',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                    'reference_key' => 'mcv',
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'hematocrit' => 42,
            'rbc' => 5,
        ]);

        $this->assertNull($result['outputs']['mcv']['reference']);
    }

    public function test_output_reference_is_null_without_reference_key(): void
    {
        $calculator = Calculator::factory()->create();

        $result = $this->service->run($calculator, [
            'hematocrit' => 42,
            'rbc' => 5,
        ]);

        $this->assertNull($result['outputs']['mcv']['reference']);
    }

    public function test_output_flags_high_value_against_reference_range(): void
    {
        ReferenceRange::create([
            'name' => 'MCV - Adult',
            'slug' => 'mcv-adult',
            'analyte_name' => 'MCV',
            'parameter_key' => 'mcv',
            'sex' => 'any',
            'unit' => 'fL',
            'lower_limit' => 80,
            'upper_limit' => 100,
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $calculator = Calculator::factory()->create([
            'output_schema' => [
                [
                    'key' => 'mcv',
                    'label' => 'MCV',
                    'unit' => 'fL',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                    'reference_key' => 'mcv',
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'hematocrit' => 60,
            'rbc' => 5,
        ]);

        $this->assertSame('high', $result['outputs']['mcv']['reference']['flag']);
    }

    public function test_service_runs_anion_gap_engine_with_select_inputs(): void
    {
        $calculator = Calculator::factory()->create([
            'engine_reference' => AnionGapCalculator::class,
            'input_schema' => [
                [
                    'key' => 'sodium',
                    'label' => 'Serum sodium',
                    'type' => 'number',
                    'required' => true,
                ],
                [
                    'key' => 'chloride',
                    'label' => 'Serum chloride',
                    'type' => 'number',
                    'required' => true,
                ],
                [
                    'key' => 'bicarbonate',
                    'label' => 'Serum bicarbonate',
                    'type' => 'number',
                    'required' => true,
                ],
                [
                    'key' => 'potassium_mode',
                    'label' => 'Potassium',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        ['value' => 'without', 'label' => 'Exclude potassium'],
                        ['value' => 'with', 'label' => 'Include potassium'],
                    ],
                ],
                [
                    'key' => 'potassium',
                    'label' => 'Serum potassium',
                    'type' => 'number',
                    'required' => false,
                ],
                [
                    'key' => 'albumin_corrected',
                    'label' => 'Albumin correction',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        ['value' => 'no', 'label' => 'No'],
                        ['value' => 'yes', 'label' => 'Yes'],
                    ],
                ],
                [
                    'key' => 'albumin',
                    'label' => 'Serum albumin',
                    'type' => 'number',
                    'required' => false,
                ],
            ],
            'output_schema' => [
                [
                    'key' => 'anion_gap',
                    'label' => 'Anion Gap',
                    'unit' => 'mEq/L',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                ],
                [
                    'key' => 'corrected_anion_gap',
                    'label' => 'Albumin-Corrected Anion Gap',
                    'unit' => 'mEq/L',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'sodium' => 140,
            'chloride' => 104,
            'bicarbonate' => 24,
            'potassium_mode' => 'with',
            'potassium' => 4,
            'albumin_corrected' => 'yes',
            'albumin' => 3,
        ]);

        $this->assertSame('16.0', $result['outputs']['anion_gap']['value']);
        $this->assertSame('18.5', $result['outputs']['corrected_anion_gap']['value']);
        $this->assertEmpty($result['errors']);
    }

    public function test_service_runs_ckd_epi_2021_engine(): void
    {
        $calculator = Calculator::factory()->create([
            'engine_reference' => CkdEpi2021Calculator::class,
            'input_schema' => [
                [
                    'key' => 'sex',
                    'label' => 'Sex',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        ['value' => 'male', 'label' => 'Male'],
                        ['value' => 'female', 'label' => 'Female'],
                    ],
                ],
                [
                    'key' => 'age',
                    'label' => 'Age',
                    'type' => 'number',
                    'required' => true,
                ],
                [
                    'key' => 'serum_creatinine',
                    'label' => 'Serum creatinine',
                    'type' => 'number',
                    'required' => true,
                ],
            ],
            'output_schema' => [
                [
                    'key' => 'egfr',
                    'label' => 'Estimated GFR',
                    'unit' => 'mL/min/1.73 m²',
                    'rounding' => 'round_half_up',
                    'precision' => 0,
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'sex' => 'male',
            'age' => 50,
            'serum_creatinine' => 1.0,
        ]);

        $this->assertSame('92', $result['outputs']['egfr']['value']);
        $this->assertEmpty($result['errors']);
    }

    public function test_service_rejects_invalid_select_value(): void
    {
        $calculator = Calculator::factory()->create([
            'engine_reference' => AnionGapCalculator::class,
            'input_schema' => [
                [
                    'key' => 'sodium',
                    'label' => 'Serum sodium',
                    'type' => 'number',
                    'required' => true,
                ],
                [
                    'key' => 'chloride',
                    'label' => 'Serum chloride',
                    'type' => 'number',
                    'required' => true,
                ],
                [
                    'key' => 'bicarbonate',
                    'label' => 'Serum bicarbonate',
                    'type' => 'number',
                    'required' => true,
                ],
                [
                    'key' => 'potassium_mode',
                    'label' => 'Potassium',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        ['value' => 'without', 'label' => 'Exclude potassium'],
                        ['value' => 'with', 'label' => 'Include potassium'],
                    ],
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'sodium' => 140,
            'chloride' => 104,
            'bicarbonate' => 24,
            'potassium_mode' => 'sometimes',
        ]);

        $this->assertEmpty($result['outputs']);
        $this->assertArrayHasKey('potassium_mode', $result['errors']);
    }

    public function test_service_runs_fib4_engine(): void
    {
        $calculator = Calculator::factory()->create([
            'engine_reference' => Fib4Calculator::class,
            'input_schema' => [
                ['key' => 'age', 'label' => 'Age', 'type' => 'number', 'required' => true],
                ['key' => 'ast', 'label' => 'AST', 'type' => 'number', 'required' => true],
                ['key' => 'alt', 'label' => 'ALT', 'type' => 'number', 'required' => true],
                ['key' => 'platelet_count', 'label' => 'Platelet count', 'type' => 'number', 'required' => true],
            ],
            'output_schema' => [
                [
                    'key' => 'fib4',
                    'label' => 'FIB-4 Index',
                    'unit' => 'score',
                    'rounding' => 'round_half_up',
                    'precision' => 3,
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'age' => 50,
            'ast' => 100,
            'alt' => 80,
            'platelet_count' => 150,
        ]);

        $this->assertSame('3.727', $result['outputs']['fib4']['value']);
        $this->assertEmpty($result['errors']);
    }

    public function test_service_runs_nafld_fibrosis_score_engine_with_select(): void
    {
        $calculator = Calculator::factory()->create([
            'engine_reference' => NafldFibrosisScoreCalculator::class,
            'input_schema' => [
                ['key' => 'age', 'label' => 'Age', 'type' => 'number', 'required' => true],
                ['key' => 'bmi', 'label' => 'BMI', 'type' => 'number', 'required' => true],
                [
                    'key' => 'impaired_fasting_glucose',
                    'label' => 'Impaired fasting glucose or diabetes',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        ['value' => 'no', 'label' => 'No'],
                        ['value' => 'yes', 'label' => 'Yes'],
                    ],
                ],
                ['key' => 'ast', 'label' => 'AST', 'type' => 'number', 'required' => true],
                ['key' => 'alt', 'label' => 'ALT', 'type' => 'number', 'required' => true],
                ['key' => 'platelet_count', 'label' => 'Platelet count', 'type' => 'number', 'required' => true],
                ['key' => 'albumin', 'label' => 'Serum albumin', 'type' => 'number', 'required' => true],
            ],
            'output_schema' => [
                [
                    'key' => 'nafld_fibrosis_score',
                    'label' => 'NAFLD Fibrosis Score',
                    'unit' => 'score',
                    'rounding' => 'round_half_up',
                    'precision' => 3,
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'age' => 50,
            'bmi' => 28,
            'impaired_fasting_glucose' => 'no',
            'ast' => 40,
            'alt' => 30,
            'platelet_count' => 200,
            'albumin' => 4.0,
        ]);

        $this->assertSame('-1.113', $result['outputs']['nafld_fibrosis_score']['value']);
        $this->assertEmpty($result['errors']);
    }

    public function test_service_runs_bilirubin_ratio_engine_with_multiple_outputs(): void
    {
        $calculator = Calculator::factory()->create([
            'engine_reference' => BilirubinRatioCalculator::class,
            'input_schema' => [
                ['key' => 'total_bilirubin', 'label' => 'Total bilirubin', 'type' => 'number', 'required' => true],
                ['key' => 'direct_bilirubin', 'label' => 'Direct bilirubin', 'type' => 'number', 'required' => true],
            ],
            'output_schema' => [
                [
                    'key' => 'indirect_bilirubin',
                    'label' => 'Indirect Bilirubin',
                    'unit' => 'mg/dL',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                ],
                [
                    'key' => 'direct_total_ratio',
                    'label' => 'Direct / Total Ratio',
                    'unit' => '%',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'total_bilirubin' => 1.2,
            'direct_bilirubin' => 0.3,
        ]);

        $this->assertSame('0.9', $result['outputs']['indirect_bilirubin']['value']);
        $this->assertSame('25.0', $result['outputs']['direct_total_ratio']['value']);
        $this->assertEmpty($result['errors']);
    }

    public function test_service_links_globulin_output_to_reference_range(): void
    {
        ReferenceRange::create([
            'name' => 'Globulin - Adult',
            'slug' => 'globulin-adult',
            'analyte_name' => 'Globulin',
            'parameter_key' => 'globulin',
            'sex' => 'any',
            'unit' => 'g/dL',
            'lower_limit' => 2.0,
            'upper_limit' => 3.5,
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $calculator = Calculator::factory()->create([
            'engine_reference' => GlobulinCalculator::class,
            'input_schema' => [
                ['key' => 'total_protein', 'label' => 'Total protein', 'type' => 'number', 'required' => true],
                ['key' => 'albumin', 'label' => 'Albumin', 'type' => 'number', 'required' => true],
            ],
            'output_schema' => [
                [
                    'key' => 'globulin',
                    'label' => 'Globulin',
                    'unit' => 'g/dL',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                    'reference_key' => 'globulin',
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'total_protein' => 7.0,
            'albumin' => 4.0,
        ]);

        $this->assertSame('3.0', $result['outputs']['globulin']['value']);
        $this->assertSame('2–3.5 g/dL', $result['outputs']['globulin']['reference']['display']);
        $this->assertSame('normal', $result['outputs']['globulin']['reference']['flag']);
    }

    public function test_service_flags_high_globulin_against_reference_range(): void
    {
        ReferenceRange::create([
            'name' => 'Globulin - Adult',
            'slug' => 'globulin-adult',
            'analyte_name' => 'Globulin',
            'parameter_key' => 'globulin',
            'sex' => 'any',
            'unit' => 'g/dL',
            'lower_limit' => 2.0,
            'upper_limit' => 3.5,
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $calculator = Calculator::factory()->create([
            'engine_reference' => GlobulinCalculator::class,
            'input_schema' => [
                ['key' => 'total_protein', 'label' => 'Total protein', 'type' => 'number', 'required' => true],
                ['key' => 'albumin', 'label' => 'Albumin', 'type' => 'number', 'required' => true],
            ],
            'output_schema' => [
                [
                    'key' => 'globulin',
                    'label' => 'Globulin',
                    'unit' => 'g/dL',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                    'reference_key' => 'globulin',
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'total_protein' => 7.0,
            'albumin' => 2.5,
        ]);

        $this->assertSame('high', $result['outputs']['globulin']['reference']['flag']);
    }

    public function test_service_links_indirect_bilirubin_output_to_reference_range(): void
    {
        ReferenceRange::create([
            'name' => 'Indirect Bilirubin - Adult',
            'slug' => 'indirect-bilirubin-adult',
            'analyte_name' => 'Indirect Bilirubin',
            'parameter_key' => 'indirect_bilirubin',
            'sex' => 'any',
            'unit' => 'mg/dL',
            'lower_limit' => 0.1,
            'upper_limit' => 1.0,
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $calculator = Calculator::factory()->create([
            'engine_reference' => BilirubinRatioCalculator::class,
            'input_schema' => [
                ['key' => 'total_bilirubin', 'label' => 'Total bilirubin', 'type' => 'number', 'required' => true],
                ['key' => 'direct_bilirubin', 'label' => 'Direct bilirubin', 'type' => 'number', 'required' => true],
            ],
            'output_schema' => [
                [
                    'key' => 'indirect_bilirubin',
                    'label' => 'Indirect Bilirubin',
                    'unit' => 'mg/dL',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                    'reference_key' => 'indirect_bilirubin',
                ],
                [
                    'key' => 'direct_total_ratio',
                    'label' => 'Direct / Total Ratio',
                    'unit' => '%',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'total_bilirubin' => 1.2,
            'direct_bilirubin' => 0.3,
        ]);

        $this->assertSame('normal', $result['outputs']['indirect_bilirubin']['reference']['flag']);
        $this->assertNull($result['outputs']['direct_total_ratio']['reference']);
    }

    public function test_service_flags_high_indirect_bilirubin_against_reference_range(): void
    {
        ReferenceRange::create([
            'name' => 'Indirect Bilirubin - Adult',
            'slug' => 'indirect-bilirubin-adult',
            'analyte_name' => 'Indirect Bilirubin',
            'parameter_key' => 'indirect_bilirubin',
            'sex' => 'any',
            'unit' => 'mg/dL',
            'lower_limit' => 0.1,
            'upper_limit' => 1.0,
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $calculator = Calculator::factory()->create([
            'engine_reference' => BilirubinRatioCalculator::class,
            'input_schema' => [
                ['key' => 'total_bilirubin', 'label' => 'Total bilirubin', 'type' => 'number', 'required' => true],
                ['key' => 'direct_bilirubin', 'label' => 'Direct bilirubin', 'type' => 'number', 'required' => true],
            ],
            'output_schema' => [
                [
                    'key' => 'indirect_bilirubin',
                    'label' => 'Indirect Bilirubin',
                    'unit' => 'mg/dL',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                    'reference_key' => 'indirect_bilirubin',
                ],
            ],
        ]);

        $result = $this->service->run($calculator, [
            'total_bilirubin' => 3.0,
            'direct_bilirubin' => 0.4,
        ]);

        $this->assertSame('2.6', $result['outputs']['indirect_bilirubin']['value']);
        $this->assertSame('high', $result['outputs']['indirect_bilirubin']['reference']['flag']);
    }
}
