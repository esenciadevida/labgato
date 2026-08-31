<?php

namespace Tests\Unit\Services;

use App\Services\LabTestsGuideService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LabTestsGuideServiceTest extends TestCase
{
    private LabTestsGuideService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LabTestsGuideService::class);
    }

    public function test_inventory_is_loaded_from_dump_file(): void
    {
        $this->assertTrue(File::exists(base_path('dump/labtestsguide_inventory.json')));

        $this->assertNotEmpty($this->service->inventory());
    }

    public function test_find_for_slug_returns_mapped_entry(): void
    {
        $entry = $this->service->findForSlug('reference_range', 'calcium-adult');

        $this->assertNotNull($entry);
        $this->assertSame('calcium-adult', $entry['labgoto_slug']);
        $this->assertSame('https://www.labtestsguide.com/calcium', $entry['labtestsguide_url']);
    }

    public function test_find_for_slug_returns_null_for_absent_labtestsguide_page(): void
    {
        $this->assertNull($this->service->findForSlug('reference_range', 'inr-adult'));
    }

    public function test_find_for_slug_returns_null_for_unknown_slug(): void
    {
        $this->assertNull($this->service->findForSlug('reference_range', 'does-not-exist'));
    }

    public function test_find_for_slug_filters_by_type(): void
    {
        $this->assertNull($this->service->findForSlug('sop', 'calcium-adult'));
    }

    public function test_inventory_contains_only_expected_types(): void
    {
        $types = $this->service->inventory()->pluck('labgoto_type')->unique();

        $this->assertTrue($types->contains('reference_range'));
        $this->assertTrue($types->contains('sop'));
        $this->assertFalse($types->contains('knowledge'));
    }
}
