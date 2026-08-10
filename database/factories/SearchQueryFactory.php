<?php

namespace Database\Factories;

use App\Models\SearchQuery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchQuery>
 */
class SearchQueryFactory extends Factory
{
    protected $model = SearchQuery::class;

    public function definition(): array
    {
        return [
            'query' => fake()->word(),
            'results_count' => fake()->numberBetween(0, 50),
            'user_id' => null,
            'ip_address' => null,
            'user_agent' => null,
        ];
    }
}
