<?php

namespace Tests\Feature;

use App\Models\Calculator;
use App\Models\Category;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\ReferenceRange;
use App\Models\RecentlyViewed;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalizationTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::factory()->member()->create(['is_active' => true]);
    }

    private function publishedCalculator(Category $category): Calculator
    {
        $calculator = Calculator::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $calculator->categories()->attach($category);

        return $calculator;
    }

    private function publishedTool(Category $category): LaboratoryTool
    {
        $tool = LaboratoryTool::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $tool->categories()->attach($category);

        return $tool;
    }

    /** @test */
    public function test_viewing_resource_as_member_records_recently_viewed(): void
    {
        $user = $this->member();
        $category = Category::factory()->create(['is_active' => true]);
        $calculator = $this->publishedCalculator($category);

        $this->actingAs($user)->get(route('public.calculators.show', $calculator->slug));

        $this->assertDatabaseHas('recently_viewed', [
            'user_id' => $user->id,
            'resource_type' => 'calculator',
            'resource_id' => $calculator->id,
        ]);
    }

    /** @test */
    public function test_viewing_resource_as_guest_does_not_record_recently_viewed(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $calculator = $this->publishedCalculator($category);

        $this->get(route('public.calculators.show', $calculator->slug));

        $this->assertDatabaseEmpty('recently_viewed');
    }

    /** @test */
    public function test_recently_viewed_is_limited_to_20_per_user(): void
    {
        $user = $this->member();
        $category = Category::factory()->create(['is_active' => true]);

        for ($i = 0; $i < 25; $i++) {
            $calculator = $this->publishedCalculator($category);
            $this->actingAs($user)->get(route('public.calculators.show', $calculator->slug));
        }

        $this->assertCount(20, RecentlyViewed::where('user_id', $user->id)->get());
    }

    /** @test */
    public function test_recently_viewed_excludes_unpublished_resources(): void
    {
        $user = $this->member();
        $category = Category::factory()->create(['is_active' => true]);
        $draft = Calculator::factory()->draft()->create();
        $draft->categories()->attach($category);

        $this->actingAs($user)->get(route('public.calculators.show', $draft->slug));

        $this->assertDatabaseEmpty('recently_viewed');
    }

    /** @test */
    public function test_recommendation_returns_resources_in_matching_categories(): void
    {
        $user = $this->member();
        $category = Category::factory()->create(['is_active' => true]);
        $viewed = $this->publishedCalculator($category);

        $this->actingAs($user)->get(route('public.calculators.show', $viewed->slug));

        $other = $this->publishedCalculator($category);

        $service = new \App\Services\RecommendationService();
        $results = $service->getRecommendedFor($user, 6);

        $this->assertTrue($results->contains('id', $other->id));
    }

    /** @test */
    public function test_recommendation_excludes_recently_viewed_resources(): void
    {
        $user = $this->member();
        $category = Category::factory()->create(['is_active' => true]);
        $viewed = $this->publishedCalculator($category);

        $this->actingAs($user)->get(route('public.calculators.show', $viewed->slug));

        $service = new \App\Services\RecommendationService();
        $results = $service->getRecommendedFor($user, 6);

        $this->assertFalse($results->contains('id', $viewed->id));
    }

    /** @test */
    public function test_recommendation_falls_back_to_latest_when_no_history(): void
    {
        $user = $this->member();
        $category = Category::factory()->create(['is_active' => true]);
        $latest = $this->publishedCalculator($category);

        $service = new \App\Services\RecommendationService();
        $results = $service->getRecommendedFor($user, 6);

        $this->assertTrue($results->contains('id', $latest->id));
    }

    /** @test */
    public function test_member_dashboard_shows_recently_viewed_and_recommendations(): void
    {
        $user = $this->member();
        $category = Category::factory()->create(['is_active' => true]);
        $viewed = $this->publishedCalculator($category);
        $other = $this->publishedCalculator($category);

        $this->actingAs($user)->get(route('public.calculators.show', $viewed->slug));

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Good');
        $response->assertSee('Recently Viewed');
        $response->assertSee('Recommended for you');
    }

    /** @test */
    public function test_guest_home_page_excludes_localstorage_recently_viewed_component(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Recently Viewed');
        $response->assertDontSee('labgato-recently-viewed', false);
    }
}
