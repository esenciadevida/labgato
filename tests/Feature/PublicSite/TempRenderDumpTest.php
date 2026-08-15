<?php

namespace Tests\Feature\PublicSite;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempRenderDumpTest extends TestCase
{
    use RefreshDatabase;

    public function test_dump_page_html(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $response = $this->get(route('public.calculators.show', $calculator->slug));
        file_put_contents('C:/Users/ZBOOK/AppData/Local/Temp/opencode/calc_page.html', $response->getContent());

        $this->assertTrue(true);
    }
}
