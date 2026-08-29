<?php

namespace Tests\Feature\Seeders;

use App\Enums\ResourceStatus;
use App\Models\Sop;
use Database\Seeders\MasterSopSeeder;
use Database\Seeders\ReferenceRangeDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterSopSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seedSops(): int
    {
        $this->artisan('db:seed', ['--class' => MasterSopSeeder::class])
            ->assertExitCode(0);

        return Sop::query()->count();
    }

    public function test_seeds_all_sop_records(): void
    {
        $this->assertSame(58, $this->seedSops());
    }

    public function test_maps_fields_from_json(): void
    {
        $this->seedSops();

        $sop = Sop::query()->where('slug', 'manual-complete-blood-count-cbc-fbc')->first();

        $this->assertNotNull($sop);
        $this->assertSame('Manual Complete Blood Count (CBC/FBC)', $sop->name);
        $this->assertSame('Haematology', $sop->department);
        $this->assertSame('1.0', $sop->version);
        $this->assertSame(ResourceStatus::PUBLISHED, $sop->status);
        $this->assertNotNull($sop->published_at);
        $this->assertNotNull($sop->last_verified_at);
        $this->assertNotNull($sop->reviewed_date);
        $this->assertNotNull($sop->last_reviewed_at);
        $this->assertSame('Samuel Ogundare Editorial Team', $sop->reviewer_name);
        $this->assertSame('B.MLS', $sop->reviewer_credentials);
        $this->assertNotEmpty($sop->faq);
    }

    public function test_skips_unresolved_featured_image_placeholders(): void
    {
        $this->seedSops();

        $this->assertSame(0, Sop::query()->whereNotNull('featured_image')->count());
    }

    public function test_links_categories_and_tags(): void
    {
        $this->seedSops();

        $sop = Sop::query()->where('slug', 'manual-complete-blood-count-cbc-fbc')->first();

        $this->assertTrue($sop->categories->contains(fn ($category) => $category->name === 'Haematology'));
        $this->assertTrue($sop->categories->contains(fn ($category) => $category->name === 'Manual Haematology'));
        $this->assertTrue($sop->tags->contains(fn ($tag) => $tag->name === 'CBC'));
    }

    public function test_seeds_manual_cbc_procedure(): void
    {
        $this->seedSops();

        $sop = Sop::query()->where('slug', 'manual-complete-blood-count-cbc-procedure')->first();

        $this->assertNotNull($sop);
        $this->assertSame('Manual Complete Blood Count (CBC) Procedure', $sop->name);
        $this->assertSame('Hematology', $sop->department);
        $this->assertSame(ResourceStatus::PUBLISHED, $sop->status);
        $this->assertNotNull($sop->published_at);
        $this->assertSame('Dr. [Laboratory Director Name]', $sop->reviewer_name);
        $this->assertCount(5, $sop->faq);
        $this->assertStringContainsString('# Standard Operating Procedure: Manual Complete Blood Count (CBC)', (string) $sop->content);
        $this->assertTrue($sop->categories->contains(fn ($category) => $category->name === 'Laboratory Techniques'));
    }

    public function test_thyroid_and_uric_acid_sops_link_canonical_ranges(): void
    {
        $this->seed(ReferenceRangeDataSeeder::class);
        $this->seedSops();

        $thyroid = Sop::query()->where('slug', 'thyroid-function-tests-tsh-ft4-ft3')->first();

        $this->assertNotNull($thyroid);
        $this->assertCount(3, $thyroid->referenceRanges);
        $this->assertSame(
            ['free_t3', 'free_t4', 'tsh'],
            $thyroid->referenceRanges->pluck('parameter_key')->sort()->values()->all()
        );

        $uric = Sop::query()->where('slug', 'uric-acid-serum-testing')->first();

        $this->assertNotNull($uric);
        $this->assertSame(
            ['uric-acid-adult-female', 'uric-acid-adult-male'],
            $uric->referenceRanges->pluck('slug')->sort()->values()->all()
        );
    }
}