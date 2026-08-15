<?php

namespace Database\Factories;

use App\Enums\ResourceStatus;
use App\Models\ReferenceRange;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ReferenceRange>
 */
class ReferenceRangeFactory extends Factory
{
    protected $model = ReferenceRange::class;

    public function definition(): array
    {
        $analyte = fake()->randomElement(['Hemoglobin', 'Glucose', 'Sodium', 'Potassium', 'Creatinine']);
        $name = $analyte.' - '.fake()->randomElement(['Adult Male', 'Adult Female', 'Adult', 'Pediatric']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'short_description' => fake()->sentence(),
            'analyte_name' => $analyte,
            'specimen_type' => fake()->randomElement(['Serum', 'Plasma', 'Whole Blood']),
            'methodology' => fake()->optional()->words(3, true),
            'population_group' => fake()->optional()->randomElement(['Adult', 'Pediatric', 'Geriatric', 'Pregnant']),
            'age_min' => null,
            'age_max' => null,
            'sex' => 'any',
            'unit' => 'g/dL',
            'lower_limit' => 12.0,
            'upper_limit' => 16.0,
            'critical_low' => 6.0,
            'critical_high' => 20.0,
            'conversion_factor' => null,
            'source_citation' => fake()->optional()->sentence(),
            'effective_date' => fake()->optional()->date(),
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

    public function forAnalyte(string $analyteName): static
    {
        return $this->state(fn () => ['analyte_name' => $analyteName]);
    }
}
