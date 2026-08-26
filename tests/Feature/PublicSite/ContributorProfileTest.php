<?php

namespace Tests\Feature\PublicSite;

use App\Enums\ResourceStatus;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContributorProfileTest extends TestCase
{
    use RefreshDatabase;

    private function contributor(array $attributes = []): User
    {
        return User::factory()->member()->create(array_merge([
            'name' => 'Jane Smith',
            'profile_slug' => 'jane-smith',
            'bio' => 'Hematology specialist with a focus on quality control.',
            'is_active' => true,
        ], $attributes));
    }

    private function publishedSop(User $user, array $attributes = []): Sop
    {
        return Sop::factory()->create(array_merge([
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now()->subDays(5),
            'contributed_by' => $user->id,
        ], $attributes));
    }

    private function publishedKnowledge(User $user, array $attributes = []): KnowledgeResource
    {
        return KnowledgeResource::factory()->create(array_merge([
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now()->subDays(3),
            'contributed_by' => $user->id,
        ], $attributes));
    }

    private function publishedInterpretation(User $user, array $attributes = []): InterpretationResource
    {
        return InterpretationResource::factory()->create(array_merge([
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => now()->subDays(1),
            'contributed_by' => $user->id,
        ], $attributes));
    }

    public function test_active_member_profile_renders_at_contributors_slug(): void
    {
        $user = $this->contributor();
        $this->publishedSop($user);

        $response = $this->get(route('contributors.show', $user->profile_slug));

        $response->assertStatus(200);
        $response->assertSee('Jane Smith');
    }

    public function test_profile_displays_display_name_bio_and_member_since(): void
    {
        $user = $this->contributor();
        $this->publishedSop($user);

        $response = $this->get(route('contributors.show', $user->profile_slug));

        $response->assertStatus(200);
        $response->assertSee('Jane Smith');
        $response->assertSee('Hematology specialist with a focus on quality control.');
        $response->assertSee('Member since '.$user->created_at->format('F Y'));
    }

    public function test_profile_lists_only_published_contributions(): void
    {
        $user = $this->contributor();
        $this->publishedSop($user, ['name' => 'Published Venipuncture Procedure']);
        $this->publishedKnowledge($user, ['name' => 'Published Hematology Overview']);

        Sop::factory()->create([
            'name' => 'Draft SOP',
            'status' => ResourceStatus::DRAFT,
            'contributed_by' => $user->id,
        ]);
        KnowledgeResource::factory()->create([
            'name' => 'Pending Knowledge Article',
            'status' => ResourceStatus::UNDER_REVIEW,
            'contributed_by' => $user->id,
        ]);
        InterpretationResource::factory()->create([
            'name' => 'Rejected Interpretation Guide',
            'status' => ResourceStatus::REJECTED,
            'contributed_by' => $user->id,
        ]);

        $response = $this->get(route('contributors.show', $user->profile_slug));

        $response->assertStatus(200);
        $response->assertSee('Published Venipuncture Procedure');
        $response->assertSee('Published Hematology Overview');
        $response->assertDontSee('Draft SOP');
        $response->assertDontSee('Pending Knowledge Article');
        $response->assertDontSee('Rejected Interpretation Guide');
    }

    public function test_profile_lists_contributions_across_all_types(): void
    {
        $user = $this->contributor();
        $this->publishedSop($user);
        $this->publishedKnowledge($user);
        $this->publishedInterpretation($user);

        $response = $this->get(route('contributors.show', $user->profile_slug));

        $response->assertStatus(200);
        $response->assertSee('SOP');
        $response->assertSee('Knowledge');
        $response->assertSee('Interpretation');
    }

    public function test_deactivated_member_profile_returns_404(): void
    {
        $user = $this->contributor(['is_active' => false]);
        $this->publishedSop($user);

        $response = $this->get(route('contributors.show', $user->profile_slug));

        $response->assertStatus(404);
    }

    public function test_profile_with_zero_published_contributions_shows_empty_state(): void
    {
        $user = $this->contributor();
        Sop::factory()->create([
            'name' => 'Only Draft',
            'status' => ResourceStatus::DRAFT,
            'contributed_by' => $user->id,
        ]);

        $response = $this->get(route('contributors.show', $user->profile_slug));

        $response->assertStatus(200);
        $response->assertSee('No published contributions');
        $response->assertSee($user->name);
    }

    public function test_nonexistent_profile_slug_returns_404(): void
    {
        $response = $this->get(route('contributors.show', 'does-not-exist'));

        $response->assertStatus(404);
    }

    public function test_profile_includes_json_ld_person_schema(): void
    {
        $user = $this->contributor();
        $this->publishedSop($user);

        $response = $this->get(route('contributors.show', $user->profile_slug));

        $response->assertStatus(200);
        $response->assertSee('"@type": "ProfilePage"', false);
        $response->assertSee('"@type": "Person"', false);
        $response->assertSee('"name": "Jane Smith"', false);
    }

    public function test_profile_is_indexable(): void
    {
        $user = $this->contributor();
        $this->publishedSop($user);

        $response = $this->get(route('contributors.show', $user->profile_slug));

        $response->assertStatus(200);
        $response->assertDontSee('noindex');
    }
}
