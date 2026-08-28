<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);

        return [
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'summary' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status' => 'draft',
            'sort_order' => 0,
            'certificate_enabled' => true,
            'certificate_requires_all' => true,
        ];
    }
}