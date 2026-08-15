<?php

namespace Tests\Feature\Security;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CachePublicPagesTest extends TestCase
{
    use RefreshDatabase;

    private function assertCacheControl(TestResponse $response, string $maxAge): void
    {
        $value = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $value);
        $this->assertStringContainsString('max-age='.$maxAge, $value);
    }

    public function test_public_page_is_cached_for_guests(): void
    {
        $this->assertCacheControl($this->get(route('home'))->assertOk(), '60');
    }

    public function test_robots_txt_is_cached_for_a_long_period(): void
    {
        $this->assertCacheControl($this->get(route('robots'))->assertOk(), '86400');
    }

    public function test_sitemap_is_cached_for_a_long_period(): void
    {
        $this->artisan('sitemap:generate');

        $this->assertCacheControl($this->get(route('sitemap'))->assertOk(), '86400');
    }

    public function test_public_page_is_not_cached_for_authenticated_users(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'dashboard.view']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-cache, private');
    }

    public function test_admin_pages_are_never_cached(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'dashboard.view']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-cache, private');
    }

    public function test_error_responses_are_not_cached(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertHeader('Cache-Control', 'no-cache, private');
    }

    protected function tearDown(): void
    {
        @unlink(public_path('sitemap.xml'));
        parent::tearDown();
    }
}
