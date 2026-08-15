<?php

namespace Database\Factories;

use App\Enums\ResourceStatus;
use App\Models\InterpretationResource;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<InterpretationResource>
 */
class InterpretationResourceFactory extends Factory
{
    protected $model = InterpretationResource::class;

    public function definition(): array
    {
        $name = fake()->randomElement([
            'Interpreting Elevated Liver Enzymes',
            'Anemia Indices: Interpreting the CBC',
            'Hyponatremia: A Practical Interpretation Guide',
            'Interpreting Thyroid Function Tests',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'short_description' => fake()->sentence(),
            'content' => "# {$name}\n\n".fake()->paragraphs(3, true),
            'clinical_context' => fake()->paragraph(),
            'related_analytes' => ['Hemoglobin', 'Glucose'],
            'mandatory_disclaimer' => 'Educational Interpretation Only: This content is for educational purposes and explains common laboratory result patterns. It does not constitute medical advice or diagnosis. Always interpret laboratory results in the context of the patient\'s clinical condition and consult with a qualified healthcare professional.',
            'author_name' => fake()->optional()->name(),
            'featured_image' => null,
            'reading_time_minutes' => fake()->optional()->numberBetween(3, 15),
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
