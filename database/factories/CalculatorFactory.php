<?php

namespace Database\Factories;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Services\Calculators\Engines\McvCalculator;
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
            'formula_display' => 'MCV = (Hct × 10) / RBC',
            'input_schema' => [
                [
                    'key' => 'hematocrit',
                    'label' => 'Hematocrit',
                    'type' => 'number',
                    'unit' => '%',
                    'required' => true,
                    'min' => 10,
                    'max' => 70,
                    'step' => 0.1,
                ],
                [
                    'key' => 'rbc',
                    'label' => 'RBC count',
                    'type' => 'number',
                    'unit' => '10^12/L',
                    'required' => true,
                    'min' => 0.5,
                    'max' => 10,
                    'step' => 0.01,
                ],
            ],
            'output_schema' => [
                [
                    'key' => 'mcv',
                    'label' => 'MCV',
                    'unit' => 'fL',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                ],
            ],
            'engine' => 'php_class',
            'engine_reference' => McvCalculator::class,
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
