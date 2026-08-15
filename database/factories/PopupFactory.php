<?php

namespace Database\Factories;

use App\Models\Popup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Popup>
 */
class PopupFactory extends Factory
{
    protected $model = Popup::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'cta_text' => null,
            'cta_url' => null,
            'trigger_type' => Popup::TRIGGER_ON_LOAD,
            'delay_seconds' => 0,
            'is_active' => false,
            'is_dismissible' => true,
            'starts_at' => null,
            'ends_at' => null,
            'created_by' => null,
        ];
    }
}
