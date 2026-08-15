<?php

namespace Tests\Feature\Admin\Ai;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiGenerateButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_button_renders_livewire_component(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $role->permissions()->attach(Permission::factory()->create(['slug' => 'sops.create']));
        $role->permissions()->attach(Permission::factory()->create(['slug' => 'ai.use']));
        $user->roles()->attach($role);
        $this->actingAs($user);

        $response = $this->get(route('admin.sops.create'));

        $response->assertOk();
        $response->assertSee('wire:snapshot');
    }
}
