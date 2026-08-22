<?php

namespace Tests\Feature\PublicSite\Calculators;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculator_listing_renders(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.calculators.index'));

        $response->assertStatus(200);
        $response->assertSee('Calculators');
        $response->assertSee($calculator->name);
    }

    public function test_calculator_listing_displays_only_published_calculators(): void
    {
        $published = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $draft = Calculator::factory()->draft()->create();
        $archived = Calculator::factory()->archived()->create();
        $obsolete = Calculator::factory()->obsolete()->create();

        $response = $this->get(route('public.calculators.index'));

        $response->assertStatus(200);
        $response->assertSee($published->name);
        $response->assertDontSee($draft->name);
        $response->assertDontSee($archived->name);
        $response->assertDontSee($obsolete->name);
    }

    public function test_calculator_listing_displays_empty_state_when_no_published_calculators_exist(): void
    {
        Calculator::factory()->draft()->create();

        $response = $this->get(route('public.calculators.index'));

        $response->assertStatus(200);
        $response->assertSee('No results found');
    }

    public function test_calculator_listing_can_filter_by_category(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $inCategory = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $inCategory->categories()->attach($category);
        $other = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.calculators.index', ['categories' => [$category->id]]));

        $response->assertStatus(200);
        $response->assertSee($inCategory->name);
        $response->assertDontSee($other->name);
    }
}
