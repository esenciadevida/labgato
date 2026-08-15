<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'message' => fake()->sentence(8),
            'type' => Announcement::TYPE_INFO,
            'link_text' => null,
            'link_url' => null,
            'is_active' => false,
            'is_dismissible' => true,
            'starts_at' => null,
            'ends_at' => null,
            'created_by' => null,
        ];
    }
}
