<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeTest extends TestCase
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

    public function test_public_home_page_renders_without_errors(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Labgoto');
    }

    public function test_public_reference_ranges_page_renders_without_errors(): void
    {
        $response = $this->get(route('public.reference-ranges.index'));

        $response->assertStatus(200);
        $response->assertSee('Reference Ranges');
    }

    public function test_admin_dashboard_renders_for_authorized_user(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Labgoto');
    }

    public function test_theme_toggle_is_present_in_public_header(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Switch to dark mode', false);
        $response->assertSee('Switch to light mode', false);
    }

    public function test_theme_toggle_is_present_in_admin_header(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Switch to dark mode', false);
        $response->assertSee('Switch to light mode', false);
    }

    public function test_theme_initialization_script_is_in_head(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('labgoto-theme', false);
        $response->assertSee('prefers-color-scheme: dark', false);
        $response->assertSee('document.documentElement.classList.add', false);
    }

    public function test_compiled_css_contains_dark_mode_variants(): void
    {
        $cssFiles = glob(public_path('build/assets/app-*.css'));
        $this->assertNotEmpty($cssFiles, 'No compiled CSS file found in build/assets');

        // Vite can emit multiple app chunk stylesheets (e.g. driver.js CSS is
        // split out), so inspect all of them to stay robust to glob ordering.
        $css = implode("\n", array_map(
            fn (string $path) => file_get_contents($path),
            $cssFiles,
        ));

        $this->assertStringContainsString('dark\\:bg-slate-950', $css);
        $this->assertStringContainsString('dark\\:text-slate-100', $css);
        $this->assertStringContainsString('.dark', $css);
    }
}
