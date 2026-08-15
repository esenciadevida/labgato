<?php

namespace Database\Factories;

use App\Enums\ResourceStatus;
use App\Models\LaboratoryTool;
use App\Services\Tools\Engines\DilutionCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LaboratoryTool>
 */
class LaboratoryToolFactory extends Factory
{
    protected $model = LaboratoryTool::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(),
            'formula_display' => 'C1 × V1 = C2 × V2',
            'input_schema' => [
                [
                    'key' => 'stock_concentration',
                    'label' => 'Stock Concentration (C1)',
                    'type' => 'number',
                    'unit' => 'mg/mL',
                    'required' => true,
                    'min' => 0,
                    'max' => 1000000,
                    'step' => 0.01,
                ],
                [
                    'key' => 'final_concentration',
                    'label' => 'Final Concentration (C2)',
                    'type' => 'number',
                    'unit' => 'mg/mL',
                    'required' => true,
                    'min' => 0,
                    'max' => 1000000,
                    'step' => 0.01,
                ],
                [
                    'key' => 'final_volume',
                    'label' => 'Final Volume (V2)',
                    'type' => 'number',
                    'unit' => 'mL',
                    'required' => true,
                    'min' => 0,
                    'max' => 1000000,
                    'step' => 0.1,
                ],
            ],
            'output_schema' => [
                [
                    'key' => 'stock_volume',
                    'label' => 'Stock Volume (V1)',
                    'unit' => 'mL',
                    'rounding' => 'round_half_up',
                    'precision' => 2,
                ],
                [
                    'key' => 'diluent_volume',
                    'label' => 'Diluent to Add',
                    'unit' => 'mL',
                    'rounding' => 'round_half_up',
                    'precision' => 2,
                ],
            ],
            'engine' => 'php_class',
            'engine_reference' => DilutionCalculator::class,
            'interpretation_notes' => fake()->paragraph(),
            'references' => 'General laboratory reference documentation.',
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
            'created_by' => null,
            'updated_by' => null,
            'seo_title' => null,
            'seo_description' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => ResourceStatus::DRAFT,
            'published_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => ResourceStatus::ARCHIVED,
            'published_at' => null,
        ]);
    }

    public function obsolete(): static
    {
        return $this->state(fn () => [
            'status' => ResourceStatus::OBSOLETE,
            'published_at' => null,
        ]);
    }
}
