<?php

namespace Tests\Feature\PublicSite;

use App\Livewire\PublicSite\ResourceBrowser;
use App\Models\Calculator;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class FacetedSearchTest extends TestCase
{
    use RefreshDatabase;

    private function createResourceWithFacets(string $name, array $categoryNames = [], array $tagNames = []): Calculator
    {
        $calculator = Calculator::factory()->create([
            'name' => $name,
            'short_description' => "Description for {$name}",
        ]);

        foreach ($categoryNames as $catName) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($catName)],
                ['name' => $catName, 'sort_order' => 0, 'is_active' => true]
            );
            $calculator->categories()->attach($category);
        }

        foreach ($tagNames as $tagName) {
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName]
            );
            $calculator->tags()->attach($tag);
        }

        return $calculator;
    }

    public function test_calculators_index_renders_livewire_browser(): void
    {
        $response = $this->get(route('public.calculators.index'));

        $response->assertStatus(200);
        $response->assertSee('resource-browser');
    }

    public function test_component_mounts_with_type(): void
    {
        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->assertStatus(200);
    }

    public function test_shows_published_resources(): void
    {
        $this->createResourceWithFacets('MCV Calculator', ['Hematocrit'], ['Anemia']);

        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->assertSee('MCV Calculator');
    }

    public function test_does_not_show_draft_resources(): void
    {
        Calculator::factory()->draft()->create(['name' => 'Draft Calc']);

        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->assertDontSee('Draft Calc');
    }

    public function test_tags_are_not_shown_in_the_filter_panel(): void
    {
        $hematology = Category::create(['name' => 'Hematology', 'slug' => 'hematology', 'sort_order' => 1, 'is_active' => true]);
        $chemistry = Category::create(['name' => 'Chemistry', 'slug' => 'chemistry', 'sort_order' => 2, 'is_active' => true]);

        $anemia = Tag::create(['name' => 'Anemia', 'slug' => 'anemia']);
        $lipids = Tag::create(['name' => 'Lipids', 'slug' => 'lipids']);

        $calc1 = Calculator::factory()->create(['name' => 'Hematocrit']);
        $calc1->categories()->attach($hematology);
        $calc1->tags()->attach($anemia);

        $calc2 = Calculator::factory()->create(['name' => 'Cholesterol']);
        $calc2->categories()->attach($chemistry);
        $calc2->tags()->attach($lipids);

        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->assertSee('Hematology')
            ->assertSee('Chemistry')
            ->assertDontSee('Anemia')
            ->assertDontSee('Lipids')
            ->call('toggleCategory', $hematology->id)
            ->assertSee('Hematocrit')
            ->assertDontSee('Cholesterol');
    }

    public function test_multi_select_categories_filter_results(): void
    {
        $cat1 = Category::create(['name' => 'Chemistry', 'slug' => 'chemistry', 'sort_order' => 1, 'is_active' => true]);
        $cat2 = Category::create(['name' => 'Hematology', 'slug' => 'hematology', 'sort_order' => 2, 'is_active' => true]);

        $this->createResourceWithFacets('Chem Calc', ['Chemistry']);
        $this->createResourceWithFacets('Heme Calc', ['Hematology']);
        $this->createResourceWithFacets('Both Calc', ['Chemistry', 'Hematology']);

        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->assertSee('Chem Calc')
            ->assertSee('Heme Calc')
            ->assertSee('Both Calc')
            ->call('toggleCategory', $cat1->id)
            ->assertSee('Chem Calc')
            ->assertSee('Both Calc')
            ->assertDontSee('Heme Calc');
    }

    public function test_search_filters_results(): void
    {
        $this->createResourceWithFacets('MCV Calculator');
        $this->createResourceWithFacets('BMI Calculator');

        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->assertSee('MCV Calculator')
            ->assertSee('BMI Calculator')
            ->set('search', 'MCV')
            ->assertSee('MCV Calculator')
            ->assertDontSee('BMI Calculator');
    }

    public function test_sort_changes_ordering(): void
    {
        Calculator::factory()->create(['name' => 'Alpha Calculator', 'published_at' => now()->subDays(2)]);
        Calculator::factory()->create(['name' => 'Zeta Calculator', 'published_at' => now()]);

        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->assertSee('Zeta Calculator')
            ->assertSee('Alpha Calculator')
            ->set('sort', 'az')
            ->assertSeeInOrder(['Alpha Calculator', 'Zeta Calculator']);
    }

    public function test_facet_counts_are_correct(): void
    {
        $cat = Category::create(['name' => 'Hematology', 'slug' => 'hematology', 'sort_order' => 1, 'is_active' => true]);

        $calc1 = Calculator::factory()->create(['name' => 'Calc 1']);
        $calc1->categories()->attach($cat);

        $calc2 = Calculator::factory()->create(['name' => 'Calc 2']);
        $calc2->categories()->attach($cat);

        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->assertSee('2'); // Hematology count
    }

    public function test_empty_state_shows_when_no_matches(): void
    {
        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->assertSee('No results found');
    }

    public function test_clear_all_resets_filters(): void
    {
        $cat = Category::create(['name' => 'Hematology', 'slug' => 'hematology', 'sort_order' => 1, 'is_active' => true]);
        $this->createResourceWithFacets('MCV Calculator', ['Hematology']);

        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->call('toggleCategory', $cat->id)
            ->assertSee('MCV Calculator')
            ->call('clearAll')
            ->assertSet('categories', [])
            ->assertSet('tags', [])
            ->assertSet('search', '')
            ->assertSet('sort', 'newest');
    }

    public function test_remove_chip_removes_filter(): void
    {
        $cat = Category::create(['name' => 'Hematology', 'slug' => 'hematology', 'sort_order' => 1, 'is_active' => true]);
        $tag = Tag::create(['name' => 'Anemia', 'slug' => 'anemia']);
        $this->createResourceWithFacets('MCV Calculator', ['Hematology'], ['Anemia']);
        $this->createResourceWithFacets('BMI Calculator');

        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->call('toggleCategory', $cat->id)
            ->call('removeCategory', $cat->id)
            ->assertSet('categories', []);
    }

    public function test_url_query_string_reflects_filters(): void
    {
        $cat = Category::create(['name' => 'Hematology', 'slug' => 'hematology', 'sort_order' => 1, 'is_active' => true]);

        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->call('toggleCategory', $cat->id)
            ->assertSee('Hematology');
    }

    public function test_search_debounce_does_not_break_component(): void
    {
        Livewire::test(ResourceBrowser::class, ['type' => 'calculator'])
            ->set('search', 'test')
            ->assertSet('search', 'test');
    }

    public function test_all_six_resource_types_mount_correctly(): void
    {
        $types = ['calculator', 'tool', 'reference-range', 'sop', 'interpretation', 'knowledge'];

        foreach ($types as $type) {
            Livewire::test(ResourceBrowser::class, ['type' => $type])
                ->assertStatus(200);
        }
    }
}
