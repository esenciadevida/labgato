<?php

namespace Tests\Feature\Security;

use App\Http\Middleware\CachePublicPages;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
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

    public function test_cron_endpoint_is_never_cached(): void
    {
        config(['labgoto.cron.secret' => 'test-secret']);

        Artisan::shouldReceive('call')->with('schedule:run')->andReturn(0);
        Artisan::shouldReceive('output')->andReturn('done');

        $this->get('/cron/test-secret/schedule')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-cache, private');
    }

    public function test_contact_page_with_csrf_form_is_never_cached(): void
    {
        $this->get(route('contact.create'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-cache, private');
    }

    public function test_responses_with_set_cookie_are_never_stored(): void
    {
        $middleware = new CachePublicPages;
        $route = Route::getRoutes()->getByName('home');
        $request = Request::create('/');
        $request->setRouteResolver(fn () => $route);
        $response = new Response('hello', 200, [
            'Set-Cookie' => 'session=abc; path=/; HttpOnly',
        ]);

        $result = $middleware->handle($request, fn () => $response);

        $this->assertStringContainsString('private', (string) $result->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $result->headers->get('Cache-Control'));
    }

    protected function tearDown(): void
    {
        @unlink(public_path('sitemap.xml'));
        parent::tearDown();
    }
}
