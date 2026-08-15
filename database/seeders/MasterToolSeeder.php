<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\LaboratoryTool;
use App\Models\Tag;
use App\Services\Calculators\Engines\GenericCalculatorEngine;
use App\Services\Tools\Engines\BufferPreparationCalculator;
use App\Services\Tools\Engines\ChemistryUnitConverterEngine;
use App\Services\Tools\Engines\DilutionCalculator;
use App\Services\Tools\Engines\MolarityConverter;
use App\Services\Tools\Engines\MolarityNormalityConverter;
use App\Services\Tools\Engines\NormalityConverter;
use App\Services\Tools\Engines\PercentageSolutionCalculator;
use App\Services\Tools\Engines\RpmToRcfConverter;
use App\Services\Tools\Engines\StockSolutionCalculator;
use App\Services\Tools\ToolService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MasterToolSeeder extends Seeder
{
    private const ENGINE_ALIASES = [
        'Dilution Calculator' => DilutionCalculator::class,
        'RPM ↔ RCF Converter' => RpmToRcfConverter::class,
        'RPM to RCF Converter' => RpmToRcfConverter::class,
        'Chemistry Unit Converter' => ChemistryUnitConverterEngine::class,
        'Percentage Solution Calculator' => PercentageSolutionCalculator::class,
        'Stock Solution Calculator' => StockSolutionCalculator::class,
        'Buffer Preparation Calculator' => BufferPreparationCalculator::class,
        'Molarity Converter' => MolarityConverter::class,
        'Normality Converter' => NormalityConverter::class,
        'Molarity ↔ Normality Converter' => MolarityNormalityConverter::class,
    ];

    private const TOOL_INPUT_KEY_RENAMES = [
        'Dilution Tool' => [
            'target_concentration' => 'final_concentration',
        ],
    ];

    private const TOOL_OUTPUT_SCHEMAS = [
        'Dilution Tool' => [
            ['key' => 'stock_volume', 'label' => 'Stock volume (V1)', 'unit' => 'mL', 'rounding' => 'round_half_up', 'precision' => 4],
            ['key' => 'diluent_volume', 'label' => 'Diluent to add', 'unit' => 'mL', 'rounding' => 'round_half_up', 'precision' => 4],
        ],
    ];

    private const SELECT_OPTIONS = [
        'mode' => [
            ['value' => 'rpm_to_rcf', 'label' => 'RPM to RCF'],
            ['value' => 'rcf_to_rpm', 'label' => 'RCF to RPM'],
        ],
        'volume_unit' => [
            ['value' => 'mL', 'label' => 'Milliliters (mL)'],
            ['value' => 'L', 'label' => 'Liters (L)'],
            ['value' => 'µL', 'label' => 'Microliters (µL)'],
        ],
        'convention' => [
            ['value' => 'w/v', 'label' => '% w/v (mass per volume)'],
            ['value' => 'v/v', 'label' => '% v/v (volume per volume)'],
            ['value' => 'w/w', 'label' => '% w/w (mass per mass)'],
        ],
    ];

    private const TOOL_UNIT_OPTIONS = [
        'Chemistry Unit Converter' => ['mol/L (M)', 'mmol/L', 'µmol/L', 'nmol/L', 'g/L', 'mg/L', 'µg/L', 'mg/dL', 'µg/dL', 'ng/mL'],
        'Hematology Unit Converter' => ['10⁹/L', '10¹²/L', 'g/L', 'g/dL', '%', 'fL', 'pg', 'cells/µL'],
        'Serology Unit Converter' => ['IU/mL', 'mIU/mL', 'µIU/mL', 'ng/mL', 'pg/mL', 'U/L', 'S/CO'],
        'Urinalysis Unit Converter' => ['mg/dL', 'mg/L', 'mmol/L', 'g/L', 'pH', 'SG', '/µL', '/hpf'],
        'Temperature Converter' => ['°C', '°F', 'K'],
        'Molarity Converter' => ['mol/L (M)', 'mmol/L', 'µmol/L', 'nmol/L', 'mol/m³'],
        'Normality Converter' => ['N', 'meq/L', 'eq/L'],
        'Molarity ↔ Normality Converter' => ['mol/L (M)', 'N', 'mmol/L', 'meq/L'],
        'Mass ↔ Moles Converter' => ['g', 'mg', 'µg', 'ng', 'mol', 'mmol', 'µmol', 'nmol'],
        'Concentration Converter' => ['g/L', 'mg/L', 'µg/mL', 'ng/mL', 'mg/dL', 'mmol/L', 'mol/L', '%'],
        'Percentage Solution Tool' => ['g', 'mg', 'µg', 'mL', 'µL', 'L'],
        'Stock Solution Tool' => ['g/L', 'mg/L', 'µg/L', 'ng/mL', 'mg/mL', 'µg/mL', 'g/dL', 'mol/L (M)', 'mmol/L', 'µmol/L', 'nmol/L'],
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $path = base_path('dump/labgoto_tools_seed (1).json');

        if (! File::exists($path)) {
            $this->command->warn("Tool seed file not found: {$path}");

            return;
        }

        $data = json_decode(File::get($path), true);

        if (! is_array($data) || empty($data['tools'])) {
            $this->command->warn('No tools found in seed file.');

            return;
        }

        LaboratoryTool::query()->delete();

        foreach ($data['tools'] as $item) {
            $engineReference = $this->resolveEngineReference($item['engine'] ?? null);

            $inputSchema = $this->normalizeSchema($item['inputSchema'] ?? [], $item['name']);
            $outputSchema = $this->normalizeSchema($item['outputSchema'] ?? [], $item['name'], 'output');

            if (isset(self::TOOL_OUTPUT_SCHEMAS[$item['name']])) {
                $outputSchema = self::TOOL_OUTPUT_SCHEMAS[$item['name']];
            }

            $tool = LaboratoryTool::create([
                'name' => $item['name'],
                'slug' => $item['slug'] ?? Str::slug($item['name']),
                'short_description' => $item['shortDescription'] ?? '',
                'formula_display' => $item['formulaDisplay'] ?? null,
                'engine' => 'php_class',
                'engine_reference' => $engineReference,
                'input_schema' => $inputSchema,
                'output_schema' => $outputSchema,
                'interpretation_notes' => $item['interpretationNotes'] ?? null,
                'references' => $this->encodeReferences($item['references'] ?? null),
                'status' => $item['status'] ?? 'draft',
                'seo_title' => $item['seoTitle'] ?? null,
                'seo_description' => $item['seoDescription'] ?? null,
            ]);

            if (! empty($item['categories'])) {
                foreach ($item['categories'] as $categoryName) {
                    $category = Category::firstOrCreate(
                        ['slug' => Str::slug($categoryName)],
                        ['name' => $categoryName, 'sort_order' => 0, 'is_active' => true]
                    );

                    $tool->categories()->syncWithoutDetaching([$category->id]);
                }
            }

            if (! empty($item['tags'])) {
                foreach ($item['tags'] as $tagName) {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );

                    $tool->tags()->syncWithoutDetaching([$tag->id]);
                }
            }
        }
    }

    private function resolveEngineReference(?string $engineName): string
    {
        if ($engineName === null) {
            return GenericCalculatorEngine::class;
        }

        if (isset(self::ENGINE_ALIASES[$engineName])) {
            return self::ENGINE_ALIASES[$engineName];
        }

        $class = array_search($engineName, app(ToolService::class)->availableEngines(), true);

        return $class !== false ? $class : GenericCalculatorEngine::class;
    }

    private function encodeReferences(mixed $references): ?string
    {
        if (is_array($references)) {
            return json_encode(array_values($references));
        }

        return $references !== null ? (string) $references : null;
    }

    private function normalizeSchema(array $schema, string $toolName, string $type = 'input'): array
    {
        $renames = self::TOOL_INPUT_KEY_RENAMES[$toolName] ?? [];
        $normalized = [];

        foreach ($schema as $field) {
            if (! is_array($field)) {
                $normalized[] = $field;

                continue;
            }

            if (isset($field['key'])) {
                $key = Str::snake($field['key']);
                $field['key'] = $renames[$key] ?? $key;
            }

            if ($type === 'input' && ($field['type'] ?? null) === 'select' && empty($field['options'])) {
                $field['options'] = $this->optionsFor($toolName, $field['key']);
            }

            $normalized[] = $field;
        }

        return $normalized;
    }

    private function optionsFor(string $toolName, string $key): array
    {
        if (isset(self::SELECT_OPTIONS[$key])) {
            return self::SELECT_OPTIONS[$key];
        }

        $units = self::TOOL_UNIT_OPTIONS[$toolName] ?? [];

        if ($units !== []) {
            return array_map(static fn (string $unit) => ['value' => $unit, 'label' => $unit], $units);
        }

        return [['value' => 'not_specified', 'label' => 'Not specified']];
    }
}
