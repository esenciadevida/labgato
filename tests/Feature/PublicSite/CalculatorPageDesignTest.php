<?php

namespace Tests\Feature\PublicSite;

use App\Enums\ResourceStatus;
use App\Livewire\PublicSite\Calculators\CalculatorRunner;
use App\Models\Calculator;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalculatorPageDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculator_detail_page_renders(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertOk()
            ->assertSee($calculator->name);
    }

    public function test_hero_contains_gradient_background_class(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertSee('bg-linear-to-b')
            ->assertSee('from-primary-950')
            ->assertSee('via-primary-950/60')
            ->assertSee('to-slate-950')
            ->assertDontSee('dark:from-slate-900');
    }

    public function test_hero_integrates_meta_chips(): void
    {
        $category = Category::factory()->create(['name' => 'Electrolytes']);
        $tag = Tag::factory()->create(['name' => 'Acid Base']);

        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $calculator->categories()->attach($category);
        $calculator->tags()->attach($tag);

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertSee('Electrolytes')
            ->assertSee('#Acid Base')
            ->assertSee('uppercase tracking-wider');
    }

    public function test_calculator_form_card_is_present(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertSee('Calculate')
            ->assertSee('All fields required unless noted')
            ->assertSee('grid grid-cols-1 sm:grid-cols-2 gap-5')
            ->assertSee('rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8');
    }

    public function test_results_panel_component_is_wired(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->set('values.rbc', 5)
            ->call('calculate')
            ->assertSet('hasCalculated', true)
            ->assertSee('Result')
            ->assertSee('bg-primary-50 dark:bg-primary-500/10')
            ->assertSee('text-4xl font-bold text-primary-700 dark:text-primary-300')
            ->assertSee('fL');
    }

    public function test_results_panel_lists_multiple_outputs(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->set('values.rbc', 5)
            ->call('calculate')
            ->assertSet('outputs.mcv.value', '84.0');
    }

    public function test_about_sidebar_card_is_present(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertSee('About')
            ->assertSee('Last updated')
            ->assertSee('Formula')
            ->assertSee($calculator->formula_display);
    }

    public function test_formula_and_clinical_notes_section_styled(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertSee('Formula & Clinical Notes', escape: false)
            ->assertSee('resource-content');
    }

    public function test_related_calculators_render_stacked(): void
    {
        $category = Category::factory()->create();

        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $calculator->categories()->attach($category);

        $related = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $related->categories()->attach($category);

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertSee('Related Calculators')
            ->assertSee('space-y-3')
            ->assertSee($related->name);
    }

    public function test_all_detail_pages_have_gradient_hero(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertSee('from-primary-950')
            ->assertSee('to-slate-950');
    }
}
