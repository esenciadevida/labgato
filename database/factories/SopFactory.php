<?php

namespace Database\Factories;

use App\Enums\ResourceStatus;
use App\Models\Sop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Sop>
 */
class SopFactory extends Factory
{
    protected $model = Sop::class;

    public function definition(): array
    {
        $name = fake()->randomElement([
            'Standard Venipuncture Procedure',
            'Daily Hematology Analyzer Quality Control',
            'Specimen Labeling and Handling',
            'Phlebotomy Site Selection and Preparation',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'short_description' => fake()->sentence(),
            'content' => "# {$name}\n\n".fake()->paragraphs(3, true),
            'department' => fake()->optional()->randomElement(['Hematology', 'Clinical Chemistry', 'Phlebotomy', 'Quality Assurance']),
            'version' => fake()->optional()->numerify('v#.#'),
            'author_name' => fake()->optional()->name(),
            'reviewed_date' => fake()->optional()->date(),
            'mandatory_disclaimer' => 'This standard operating procedure is provided for educational purposes and may not match the policies of your institution. Always follow your laboratory\'s approved procedures.',
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
