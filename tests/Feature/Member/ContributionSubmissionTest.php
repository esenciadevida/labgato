<?php

namespace Tests\Feature\Member;

use App\Enums\ResourceStatus;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContributionSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::factory()->member()->create(['is_active' => true]);
    }

    private function sopPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Daily Hematology Analyzer Quality Control',
            'short_description' => 'Daily quality control procedure for the hematology analyzer.',
            'purpose' => 'Ensure accurate and reliable daily results.',
            'scope' => 'Applies to all hematology laboratory staff.',
            'procedure_content' => "## Procedure\n\nRun controls before patient samples.",
            'quality_control' => '',
            'safety_information' => '',
            'references' => '',
        ], $overrides);
    }

    private function knowledgePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Understanding Complete Blood Count Results',
            'short_description' => 'A practical guide to reading and interpreting a complete blood count.',
            'content' => "# Complete Blood Count\n\nLearn how to interpret a CBC.",
            'learning_objectives' => 'Interpret a CBC report accurately.',
            'topic_category' => 'Hematology',
            'difficulty_level' => 'Intermediate',
        ], $overrides);
    }

    private function interpretationPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Interpreting Thyroid Function Test Results',
            'short_description' => 'How to interpret TSH, free T4, and free T3 results in context.',
            'content' => "# Thyroid Function\n\nInterpret TSH alongside free T4.",
            'clinical_context' => 'Used when evaluating suspected thyroid disorders.',
        ], $overrides);
    }

    public function test_member_can_create_a_draft_sop_contribution(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)->post(
            route('account.contributions.store', ['type' => 'sop']),
            $this->sopPayload(['action' => 'draft'])
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sops', [
            'name' => 'Daily Hematology Analyzer Quality Control',
            'status' => ResourceStatus::DRAFT->value,
            'contributed_by' => $user->id,
            'published_at' => null,
        ]);
    }

    public function test_member_can_create_a_draft_knowledge_contribution(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)->post(
            route('account.contributions.store', ['type' => 'knowledge']),
            $this->knowledgePayload(['action' => 'draft'])
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('knowledge_resources', [
            'name' => 'Understanding Complete Blood Count Results',
            'status' => ResourceStatus::DRAFT->value,
            'contributed_by' => $user->id,
        ]);
    }

    public function test_member_can_create_a_draft_interpretation_contribution(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)->post(
            route('account.contributions.store', ['type' => 'interpretation']),
            $this->interpretationPayload(['action' => 'draft'])
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('interpretation_resources', [
            'name' => 'Interpreting Thyroid Function Test Results',
            'status' => ResourceStatus::DRAFT->value,
            'contributed_by' => $user->id,
        ]);
    }

    public function test_member_can_submit_a_draft_for_review(): void
    {
        $user = $this->member();

        $response = $this->actingAs($user)->post(
            route('account.contributions.store', ['type' => 'sop']),
            $this->sopPayload(['action' => 'submit'])
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sops', [
            'status' => ResourceStatus::UNDER_REVIEW->value,
            'contributed_by' => $user->id,
        ]);
    }

    public function test_submission_sets_status_to_under_review_and_contributed_by(): void
    {
        $user = $this->member();

        $this->actingAs($user)->post(
            route('account.contributions.store', ['type' => 'knowledge']),
            $this->knowledgePayload(['action' => 'submit'])
        );

        $contribution = KnowledgeResource::query()->first();

        $this->assertSame(ResourceStatus::UNDER_REVIEW, $contribution->status);
        $this->assertSame($user->id, (int) $contribution->contributed_by);
    }

    public function test_member_cannot_submit_directly_to_published(): void
    {
        $user = $this->member();

        $this->actingAs($user)->post(
            route('account.contributions.store', ['type' => 'interpretation']),
            $this->interpretationPayload(['action' => 'submit'])
        );

        $contribution = InterpretationResource::query()->first();

        $this->assertSame(ResourceStatus::UNDER_REVIEW, $contribution->status);
        $this->assertNotSame(ResourceStatus::PUBLISHED, $contribution->status);
        $this->assertNull($contribution->published_at);
    }

    public function test_member_cannot_set_categories_tags_or_seo_fields(): void
    {
        $user = $this->member();

        $this->actingAs($user)->post(
            route('account.contributions.store', ['type' => 'sop']),
            $this->sopPayload([
                'action' => 'submit',
                'categories' => [1],
                'tags' => [1],
                'seo_title' => 'Hacked title',
                'seo_description' => 'Hacked description',
            ])
        );

        $contribution = Sop::query()->first();

        $this->assertNull($contribution->seo_title);
        $this->assertNull($contribution->seo_description);
        $this->assertDatabaseCount('sop_category', 0);
        $this->assertDatabaseCount('sop_tag', 0);
    }

    public function test_rich_text_is_sanitized_when_rendered(): void
    {
        $user = $this->member();

        $this->actingAs($user)->post(
            route('account.contributions.store', ['type' => 'knowledge']),
            $this->knowledgePayload([
                'action' => 'submit',
                'content' => "# Guide\n\n<script>alert('xss')</script>Safe content",
            ])
        );

        $contribution = KnowledgeResource::query()->first();

        $response = $this->actingAs($user)->get(
            route('account.contributions.show', ['type' => 'knowledge', 'id' => $contribution->id])
        );

        $response->assertStatus(200);
        $response->assertDontSee("<script>alert('xss')</script>", false);
        $response->assertSee('&lt;script&gt;alert', false);
        $response->assertSee('Safe content');
    }

    public function test_submission_is_audit_logged(): void
    {
        $user = $this->member();

        $this->actingAs($user)->post(
            route('account.contributions.store', ['type' => 'sop']),
            $this->sopPayload(['action' => 'submit'])
        );

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'contributions.submitted',
            'resource_type' => Sop::class,
        ]);
    }
}
