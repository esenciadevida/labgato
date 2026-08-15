<?php

namespace Database\Factories;

use App\Enums\ResourceStatus;
use App\Models\KnowledgeResource;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KnowledgeResource>
 */
class KnowledgeResourceFactory extends Factory
{
    protected $model = KnowledgeResource::class;

    public function definition(): array
    {
        $name = fake()->randomElement([
            'Introduction to Hematology',
            'Clinical Chemistry Overview',
            'Understanding Quality Control in the Laboratory',
            'Specimen Collection and Handling Best Practices',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'short_description' => fake()->sentence(),
            'content' => "# {$name}\n\n".fake()->paragraphs(3, true),
            'topic_category' => fake()->optional()->randomElement(['Hematology', 'Clinical Chemistry', 'Laboratory Practice']),
            'difficulty_level' => fake()->randomElement(['Beginner', 'Intermediate', 'Advanced']),
            'learning_objectives' => fake()->optional()->paragraph(),
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
