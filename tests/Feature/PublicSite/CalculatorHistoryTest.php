<?php

namespace Tests\Feature\PublicSite;

use App\Models\Calculator;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalculatorHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['is_active' => true]);
    }

    /** @test */
    public function test_calculator_page_includes_recent_calculations_component()
    {
        $calculator = Calculator::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $calculator->categories()->attach($this->category);

        $response = $this->get(route('public.calculators.show', $calculator->slug));

        $response->assertOk();
        $response->assertViewHas('calculator');
        $response->assertSee('Recent Calculations');
    }

    /** @test */
    public function test_calculator_runner_performs_calculation()
    {
        $calculator = Calculator::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $calculator->categories()->attach($this->category);

        Livewire::test('public-site.calculators.calculator-runner', ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->set('values.rbc', 5)
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSet('outputs.mcv.value', '84.0');
    }
}
