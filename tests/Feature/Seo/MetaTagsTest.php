<?php

namespace Tests\Feature\Seo;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetaTagsTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPermission(string $permissionSlug): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => $permissionSlug]);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_home_page_has_title_tag(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('<title>', false);
    }

    public function test_home_page_has_meta_description(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('meta name="description"', false);
    }

    public function test_home_page_has_canonical_url(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('rel="canonical"', false);
    }

    public function test_home_page_has_open_graph_tags(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('og:title', false);
        $response->assertSee('og:description', false);
        $response->assertSee('og:type', false);
    }

    public function test_admin_pages_are_noindex(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('noindex', false);
    }

    public function test_auth_pages_are_noindex(): void
    {
        $response = $this->get(route('auth.login'));

        $response->assertStatus(200);
        $response->assertSee('noindex', false);
    }
}
