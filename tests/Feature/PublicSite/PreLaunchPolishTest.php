<?php

namespace Tests\Feature\PublicSite;

use App\Enums\ResourceStatus;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

class PreLaunchPolishTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        @unlink(public_path('sitemap.xml'));
        parent::tearDown();
    }

    public function test_custom_error_pages_render_with_friendly_markup(): void
    {
        $this->get('/nonexistent-page-that-does-not-exist')
            ->assertStatus(404)
            ->assertSee('404')
            ->assertSee('Page Not Found')
            ->assertSee('may have moved')
            ->assertSee('Go Home')
            ->assertSee('Contact Support');

        $this->view('errors.403')
            ->assertSee('403')
            ->assertSee('Access Denied');

        $this->view('errors.500')
            ->assertSee('500')
            ->assertSee('Something Went Wrong');

        $this->view('errors.503')
            ->assertSee('503')
            ->assertSee('Maintenance');
    }

    public function test_error_pages_link_to_contact_support(): void
    {
        $this->get('/nonexistent-page-that-does-not-exist')
            ->assertSee(route('contact.create'), false);
    }

    public function test_legal_pages_include_polished_sections(): void
    {
        $this->get(route('legal.privacy-policy'))
            ->assertOk()
            ->assertSee('Cookies')
            ->assertSee('Your Rights');

        $this->get(route('legal.terms-of-service'))
            ->assertOk()
            ->assertSee('Accounts and Registration');
    }

    public function test_home_page_includes_cookie_consent_banner(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('We use cookies', false)
            ->assertSee('Accept')
            ->assertSee('Decline')
            ->assertSee(route('legal.privacy-policy'), false);
    }

    public function test_home_page_includes_open_graph_and_twitter_meta_tags(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<meta property="og:title"', false)
            ->assertSee('<meta property="og:site_name"', false)
            ->assertSee('<meta name="twitter:card"', false)
            ->assertSee('<meta name="twitter:title"', false);
    }

    public function test_sitemap_generation_includes_contact_page(): void
    {
        $this->artisan('sitemap:generate')->assertSuccessful();

        $content = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringContainsString(config('app.url').'/contact', $content);
    }

    public function test_sitemap_includes_active_contributor_profiles(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'profile_slug' => 'dr-jane-smith',
        ]);

        Sop::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'contributed_by' => $user->id,
        ]);

        $this->artisan('sitemap:generate')->assertSuccessful();

        $content = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringContainsString('/contributors/dr-jane-smith', $content);
    }

    public function test_sitemap_excludes_contributors_without_published_contributions(): void
    {
        User::factory()->create([
            'is_active' => true,
            'profile_slug' => 'dr-inactive',
        ]);

        $this->artisan('sitemap:generate')->assertSuccessful();

        $content = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringNotContainsString('/contributors/dr-inactive', $content);
    }

    public function test_sitemap_is_served_as_xml(): void
    {
        $this->artisan('sitemap:generate')->assertSuccessful();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');
    }

    public function test_backup_is_configured_and_scheduled(): void
    {
        $this->assertSame(7, config('backup.cleanup.default_strategy.keep_all_backups_for_days'));
        $this->assertContains('local', config('backup.backup.destination.disks'));
        $this->assertEquals([config('database.default')], config('backup.backup.source.databases'));

        $this->artisan('schedule:list')->assertSuccessful();

        $commands = collect(Schedule::events())
            ->map(fn ($event) => $event->command ?? '')
            ->implode(' ');

        $this->assertStringContainsString('backup:run', $commands);
        $this->assertStringContainsString('sitemap:generate', $commands);
    }
}
