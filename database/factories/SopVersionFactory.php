<?php

namespace Database\Factories;

use App\Models\Sop;
use App\Models\SopVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SopVersion>
 */
class SopVersionFactory extends Factory
{
    protected $model = SopVersion::class;

    public function definition(): array
    {
        return [
            'sop_id' => Sop::factory(),
            'version_number' => '1.0',
            'content' => fake()->paragraphs(2, true),
            'change_summary' => null,
            'created_by' => null,
        ];
    }
}
