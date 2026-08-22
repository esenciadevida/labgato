<?php

namespace Tests\Feature\Seo;

use App\Models\Calculator;
use App\Models\Category;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sop;
use App\Models\Tag;
use App\Models\User;
use App\Services\RelatedResourceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoEeatTest extends TestCase
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

    public function test_seo_fields_persist_and_render_on_public_page(): void
    {
        $user = $this->createUserWithPermission('knowledge.create');

        $response = $this->actingAs($user)->post(route('admin.knowledge.store'), [
            'name' => 'Complete Blood Count Guide',
            'slug' => 'complete-blood-count-guide',
            'content' => 'Everything about the CBC.',
            'status' => 'published',
            'seo_title' => 'CBC Test Explained',
            'seo_description' => 'A complete guide to the CBC blood test.',
            'focus_keyword' => 'cbc test',
            'categories' => [],
            'tags' => [],
        ]);

        $response->assertRedirect(route('admin.knowledge.index'));

        $knowledge = KnowledgeResource::where('slug', 'complete-blood-count-guide')->firstOrFail();
        $this->assertSame('CBC Test Explained', $knowledge->seo_title);
        $this->assertSame('cbc test', $knowledge->focus_keyword);

        $public = $this->get(route('public.knowledge.show', $knowledge->slug));
        $public->assertOk();
        $public->assertSee('<title>CBC Test Explained - '.config('app.name').'</title>', false);
        $public->assertSee('A complete guide to the CBC blood test.', false);
    }

    public function test_trust_box_renders_reviewer_details(): void
    {
        $sop = Sop::factory()->create([
            'author_name' => 'Dr. Alice Writer',
            'reviewer_name' => 'Dr. Bob Reviewer',
            'reviewer_credentials' => 'MD, FMCPath',
            'last_reviewed_at' => '2026-08-01',
        ]);

        $response = $this->get(route('public.sops.show', $sop->slug));

        $response->assertOk();
        $response->assertSee('Reviewed &amp; Trusted', false);
        $response->assertSee('Dr. Alice Writer');
        $response->assertSee('Dr. Bob Reviewer');
        $response->assertSee('MD, FMCPath');
        $response->assertSee('Aug 1, 2026');
    }

    public function test_faq_section_renders_accordion_and_schema(): void
    {
        $knowledge = KnowledgeResource::factory()->create([
            'faq' => [
                ['question' => 'What is a CBC test?', 'answer' => 'It measures blood cells.'],
                ['question' => 'Is fasting required?', 'answer' => 'Not usually.'],
            ],
        ]);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertOk();
        $response->assertSee('Frequently Asked Questions');
        $response->assertSee('What is a CBC test?');
        $response->assertSee('It measures blood cells.');
        $response->assertSee('aria-controls="faq-panel-0"', false);
        $this->assertJsonLdContainsType($response->getContent(), 'FAQPage');
    }

    public function test_faq_section_hidden_when_no_faq_data(): void
    {
        $knowledge = KnowledgeResource::factory()->create();

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertOk();
        $response->assertDontSee('Frequently Asked Questions');
        $this->assertJsonLdDoesNotContainType($response->getContent(), 'FAQPage');
    }

    public function test_related_resources_score_shared_tags_categories_and_type(): void
    {
        $tag = Tag::factory()->create();
        $category = Category::factory()->create();

        $current = KnowledgeResource::factory()->create();
        $current->tags()->attach($tag);
        $current->categories()->attach($category);

        $sameTypeSharedTag = KnowledgeResource::factory()->create();
        $sameTypeSharedTag->tags()->attach($tag);

        $otherTypeSharedCategory = InterpretationResource::factory()->create();
        $otherTypeSharedCategory->categories()->attach($category);

        Sop::factory()->count(2)->create();

        $related = app(RelatedResourceService::class)->relatedFor($current, 3);

        $this->assertCount(2, $related, 'Only resources sharing tags/categories (or same type) should be related.');

        $top = $related->first();
        $this->assertSame($sameTypeSharedTag->name, $top->title, 'Same type with shared tag (+3+1) must outrank shared category only (+2).');
        $this->assertSame('Knowledge', $top->type_label);
        $this->assertEquals(route('public.knowledge.show', $sameTypeSharedTag->slug), $top->url);

        $second = $related->skip(1)->first();
        $this->assertSame($otherTypeSharedCategory->name, $second->title);
        $this->assertSame('Interpretation', $second->type_label);
        $this->assertEquals(route('public.interpretation.show', $otherTypeSharedCategory->slug), $second->url);
    }

    public function test_show_pages_render_cross_type_related_resources(): void
    {
        $tag = Tag::factory()->create();

        $knowledge = KnowledgeResource::factory()->create();
        $knowledge->tags()->attach($tag);

        $calculator = Calculator::factory()->create(['name' => 'Anion Gap Calculator']);
        $calculator->tags()->attach($tag);

        $response = $this->get(route('public.knowledge.show', $knowledge->slug));

        $response->assertOk();
        $response->assertSee('Anion Gap Calculator');
        $response->assertSee('Calculator', false);
    }

    public function test_fallback_seo_title_uses_focus_keyword_when_seo_title_missing(): void
    {
        $interpretation = InterpretationResource::factory()->create([
            'name' => 'High Ferritin Levels',
            'seo_title' => null,
            'focus_keyword' => 'ferritin interpretation',
            'short_description' => null,
            'content' => '<p>Understanding elevated ferritin results.</p>',
            'seo_description' => null,
        ]);

        $this->assertSame(
            'High Ferritin Levels — ferritin interpretation',
            $interpretation->refresh()->effectiveSeoTitle()
        );
        $this->assertNull($interpretation->seo_title, 'Raw column must remain untouched by the fallback.');
        $this->assertSame('Understanding elevated ferritin results.', $interpretation->effectiveSeoDescription());

        $response = $this->get(route('public.interpretation.show', $interpretation->slug));

        $response->assertOk();
        $response->assertSee(
            '<title>'.e('High Ferritin Levels — ferritin interpretation - '.config('app.name')).'</title>',
            false
        );
    }

    public function test_admin_forms_include_seo_trust_and_faq_fields(): void
    {
        $pages = [
            ['route' => route('admin.knowledge.create'), 'permission' => 'knowledge.create'],
            ['route' => route('admin.sops.create'), 'permission' => 'sops.create'],
            ['route' => route('admin.interpretation.create'), 'permission' => 'interpretation.create'],
        ];

        foreach ($pages as $page) {
            $user = $this->createUserWithPermission($page['permission']);

            $response = $this->actingAs($user)->get($page['route']);

            $response->assertOk();
            $this->assertSeoEeatFormFields($response->getContent());
        }
    }

    private function assertSeoEeatFormFields(string $html): void
    {
        foreach ([
            'name="focus_keyword"',
            'name="reviewer_name"',
            'name="reviewer_credentials"',
            'name="last_reviewed_at"',
            "faq.push({ question: '', answer: '' })",
            'Add Question',
            'Trust &amp; Review',
        ] as $needle) {
            $this->assertStringContainsString($needle, $html);
        }
    }

    private function assertJsonLdContainsType(string $html, string $type): void
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

        $types = [];
        foreach ($matches[1] as $json) {
            $decoded = json_decode(trim($json), true);
            if (is_array($decoded) && isset($decoded['@type'])) {
                $types[] = $decoded['@type'];
            }
        }

        $this->assertContains($type, $types, "Expected {$type} structured data to be present.");
    }

    private function assertJsonLdDoesNotContainType(string $html, string $type): void
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

        foreach ($matches[1] as $json) {
            $decoded = json_decode(trim($json), true);
            $this->assertFalse(
                is_array($decoded) && ($decoded['@type'] ?? null) === $type,
                "Did not expect {$type} structured data."
            );
        }
    }
}
