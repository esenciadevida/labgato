<?php

namespace Database\Factories;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Services\Calculators\Engines\AnionGapCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Calculator>
 */
class CalculatorFactory extends Factory
{
    protected $model = Calculator::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(),
            'formula_display' => 'Sodium - (Chloride + Bicarbonate)',
            'input_schema' => [
                [
                    'key' => 'sodium',
                    'label' => 'Serum Sodium',
                    'type' => 'number',
                    'unit' => 'mEq/L',
                    'required' => true,
                    'min' => 100,
                    'max' => 200,
                    'step' => 0.1,
                ],
                [
                    'key' => 'chloride',
                    'label' => 'Serum Chloride',
                    'type' => 'number',
                    'unit' => 'mEq/L',
                    'required' => true,
                    'min' => 80,
                    'max' => 150,
                    'step' => 0.1,
                ],
                [
                    'key' => 'bicarbonate',
                    'label' => 'Serum Bicarbonate',
                    'type' => 'number',
                    'unit' => 'mEq/L',
                    'required' => true,
                    'min' => 5,
                    'max' => 50,
                    'step' => 0.1,
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
            ],
            'engine' => 'php_class',
            'engine_reference' => AnionGapCalculator::class,
            'interpretation_notes' => fake()->paragraph(),
            'references' => 'Clinical reference documentation.',
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
