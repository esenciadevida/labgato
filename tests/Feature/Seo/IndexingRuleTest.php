<?php

namespace Tests\Feature\Seo;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexingRuleTest extends TestCase
{
    use RefreshDatabase;

    private function userWithPermission(string $slug): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => $slug]);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_admin_pages_are_noindex(): void
    {
        $user = $this->userWithPermission('dashboard.view');

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('content="noindex, nofollow"', false);
    }

    public function test_auth_pages_are_noindex(): void
    {
        $this->get(route('auth.login'))
            ->assertOk()
            ->assertSee('content="noindex, nofollow"', false);
    }

    public function test_error_pages_are_noindex(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('content="noindex, nofollow"', false);
    }

    public function test_search_page_is_noindex(): void
    {
        $this->get(route('public.search', ['q' => 'test']))
            ->assertOk()
            ->assertSee('content="noindex, nofollow"', false);
    }

    public function test_filtered_listing_pages_are_noindex(): void
    {
        $this->get(route('public.knowledge.index', ['q' => 'test']))
            ->assertOk()
            ->assertSee('content="noindex, nofollow"', false);
    }

    public function test_plain_listing_pages_are_indexable(): void
    {
        $this->get(route('public.calculators.index'))
            ->assertOk()
            ->assertSee('content="index, follow"', false);
    }
}
