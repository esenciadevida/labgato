<?php

namespace Tests\Feature\PublicSite\Calculators;

use App\Enums\ResourceStatus;
use App\Livewire\PublicSite\Calculators\CalculatorRunner;
use App\Models\Calculator;
use App\Models\ReferenceRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalculatorReferenceRangeTest extends TestCase
{
    use RefreshDatabase;

    private function mcvCalculator(array $outputOverrides = []): Calculator
    {
        return Calculator::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'output_schema' => [
                array_merge([
                    'key' => 'mcv',
                    'label' => 'MCV',
                    'unit' => 'fL',
                    'rounding' => 'round_half_up',
                    'precision' => 1,
                ], $outputOverrides),
            ],
        ]);
    }

    private function mcvReferenceRange(): ReferenceRange
    {
        return ReferenceRange::create([
            'name' => 'MCV - Adult',
            'slug' => 'mcv-adult',
            'analyte_name' => 'MCV',
            'parameter_key' => 'mcv',
            'sex' => 'any',
            'unit' => 'fL',
            'lower_limit' => 80,
            'upper_limit' => 100,
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function test_result_shows_linked_reference_range_and_normal_flag(): void
    {
        $this->mcvReferenceRange();
        $calculator = $this->mcvCalculator(['reference_key' => 'mcv']);

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->set('values.rbc', 5)
            ->call('calculate')
            ->assertSee('Reference:')
            ->assertSee('80–100 fL')
            ->assertSee('Normal');
    }

    public function test_result_flags_high_value(): void
    {
        $this->mcvReferenceRange();
        $calculator = $this->mcvCalculator(['reference_key' => 'mcv']);

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 60)
            ->set('values.rbc', 5)
            ->call('calculate')
            ->assertSee('High')
            ->assertDontSee('Normal');
    }

    public function test_result_shows_no_reference_when_range_missing(): void
    {
        $calculator = $this->mcvCalculator(['reference_key' => 'mcv']);

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->set('values.rbc', 5)
            ->call('calculate')
            ->assertDontSee('Reference:');
    }

    public function test_result_shows_no_reference_without_reference_key(): void
    {
        $this->mcvReferenceRange();
        $calculator = $this->mcvCalculator();

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->set('values.rbc', 5)
            ->call('calculate')
            ->assertDontSee('Reference:');
    }

    public function test_reference_range_links_to_its_public_page(): void
    {
        $this->mcvReferenceRange();
        $calculator = $this->mcvCalculator(['reference_key' => 'mcv']);

        Livewire::test(CalculatorRunner::class, ['calculator' => $calculator])
            ->set('values.hematocrit', 42)
            ->set('values.rbc', 5)
            ->call('calculate')
            ->assertSee(route('public.reference-ranges.show', 'mcv-adult'));
    }

    public function test_show_page_lists_internal_reference_ranges_used(): void
    {
        $this->mcvReferenceRange();
        $calculator = $this->mcvCalculator(['reference_key' => 'mcv']);

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertOk()
            ->assertSee('Reference Ranges Used')
            ->assertSee('MCV - Adult')
            ->assertSee('80–100 fL')
            ->assertSee(route('public.reference-ranges.show', 'mcv-adult'));
    }

    public function test_show_page_omits_panel_without_reference_key(): void
    {
        $this->mcvReferenceRange();
        $calculator = $this->mcvCalculator();

        $this->get(route('public.calculators.show', $calculator->slug))
            ->assertOk()
            ->assertDontSee('Reference Ranges Used');
    }

    public function test_show_page_dedupes_repeated_reference_keys(): void
    {
        $this->mcvReferenceRange();
        $calculator = $this->mcvCalculator(['reference_key' => 'mcv']);

        $calculator->forceFill([
            'output_schema' => [
                ['key' => 'mcv', 'label' => 'MCV', 'unit' => 'fL', 'reference_key' => 'mcv'],
                ['key' => 'mch', 'label' => 'MCH', 'unit' => 'pg', 'reference_key' => 'mcv'],
            ],
        ])->save();

        $response = $this->get(route('public.calculators.show', $calculator->slug));

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'MCV - Adult'));
    }
}
