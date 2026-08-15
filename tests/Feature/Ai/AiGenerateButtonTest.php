<?php

namespace Tests\Feature\Ai;

use App\Exceptions\Ai\AiTimeoutException;
use App\Livewire\Admin\Ai\AiGenerate;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\AiResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Ai\Concerns\FakesAiProvider;
use Tests\TestCase;

class AiGenerateButtonTest extends TestCase
{
    use FakesAiProvider;
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

    private function componentParams(array $params = []): array
    {
        return array_merge([
            'useCase' => 'draft_knowledge',
            'target' => 'content',
            'label' => 'Draft with AI',
        ], $params);
    }

    public function test_component_renders_button_with_label(): void
    {
        $this->actingAs($this->createUserWithPermission('ai.use'));

        Livewire::test(AiGenerate::class, $this->componentParams())
            ->assertSee('Draft with AI');
    }

    public function test_generate_injects_sanitized_content(): void
    {
        $this->enableAiFeature();
        $this->fakeAiProvider(new AiResponse(
            content: '**Generated** draft.',
            provider: 'groq',
            model: 'test-model',
            promptTokens: 5,
            completionTokens: 4,
            latencyMs: 2,
        ));

        $this->actingAs($this->createUserWithPermission('ai.use'));

        Livewire::test(AiGenerate::class, $this->componentParams())
            ->call('generate', 'Hematology', 'RBC indices', 'Intro to hematology.')
            ->assertSet('generated', '**Generated** draft.')
            ->assertSet('error', '')
            ->assertSet('loading', false);
    }

    public function test_generate_blocks_user_without_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        Livewire::test(AiGenerate::class, $this->componentParams())
            ->call('generate', 'Hematology', '', '')
            ->assertSet('error', 'You do not have permission to use AI features.')
            ->assertSet('generated', '');
    }

    public function test_generate_blocks_when_ai_feature_is_disabled(): void
    {
        config()->set('labgoto.features.ai', false);
        config()->set('labgoto.ai.enabled', false);

        $this->actingAs($this->createUserWithPermission('ai.use'));

        Livewire::test(AiGenerate::class, $this->componentParams())
            ->call('generate', 'Hematology', '', '')
            ->assertSet('error', 'AI features are currently disabled by the administrator.');
    }

    public function test_generate_maps_timeout_to_safe_error_message(): void
    {
        $this->enableAiFeature();
        $this->throwingAiProvider(AiTimeoutException::class);

        $this->actingAs($this->createUserWithPermission('ai.use'));

        Livewire::test(AiGenerate::class, $this->componentParams())
            ->call('generate', 'Hematology', '', '')
            ->assertSet('error', 'The AI took too long to respond. Please try again.')
            ->assertSet('generated', '')
            ->assertSet('loading', false);
    }

    public function test_generate_records_usage_row(): void
    {
        $this->enableAiFeature();
        $this->fakeAiProvider(new AiResponse(
            content: 'Draft.',
            provider: 'groq',
            model: 'test-model',
            promptTokens: 2,
            completionTokens: 3,
            latencyMs: 1,
        ));

        $user = $this->createUserWithPermission('ai.use');
        $this->actingAs($user);

        Livewire::test(AiGenerate::class, $this->componentParams())
            ->call('generate', 'Hematology', '', '');

        $this->assertDatabaseHas('ai_usage', [
            'user_id' => $user->id,
            'action' => 'draft_knowledge',
            'status' => 'success',
        ]);
    }
}
