<?php

namespace Tests\Feature\PublicSite\Calculators;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_calculator_detail_renders(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.calculators.show', $calculator->slug));

        $response->assertStatus(200);
        $response->assertSee($calculator->name);
    }

    public function test_draft_calculator_returns_404(): void
    {
        $calculator = Calculator::factory()->draft()->create();

        $response = $this->get(route('public.calculators.show', $calculator->slug));

        $response->assertNotFound();
    }

    public function test_archived_calculator_returns_404(): void
    {
        $calculator = Calculator::factory()->archived()->create();

        $response = $this->get(route('public.calculators.show', $calculator->slug));

        $response->assertNotFound();
    }

    public function test_nonexistent_calculator_returns_404(): void
    {
        $response = $this->get(route('public.calculators.show', 'does-not-exist'));

        $response->assertNotFound();
    }

    public function test_calculator_detail_displays_breadcrumbs(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.calculators.show', $calculator->slug));

        $response->assertStatus(200);
        $response->assertSee('Home');
        $response->assertSee('Calculators');
        $response->assertSee($calculator->name);
    }

    public function test_calculator_detail_includes_meta_tags(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.calculators.show', $calculator->slug));

        $response->assertStatus(200);
        $response->assertSee($calculator->name.' - '.config('app.name'), false);
    }
}
