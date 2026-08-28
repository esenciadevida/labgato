<?php

namespace App\Services;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\KnowledgeResource;
use App\Models\Sop;
use App\Models\Tag;
use Illuminate\Support\Str;

/**
 * Idempotent, environment-agnostic importer for the master content library
 * (dumped SOPs and knowledge resources). Used by the CLI so master content
 * can be loaded deliberately into production without running the guarded
 * database seeders.
 */
class MasterContentImporter
{
    private const UNRESOLVED_IMAGE_MARKERS = ['', 'IMAGE_URL_TO_BE_CONFIRMED'];

    private const DIFFICULTY_LEVELS = [
        'beginner' => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
    ];

    /**
     * @param array<int, array<string, mixed>> $records
     *
     * @return array{imported: int, created: int, updated: int}
     */
    public function importSops(array $records): array
    {
        $stats = ['imported' => 0, 'created' => 0, 'updated' => 0];

        foreach ($records as $item) {
            $sop = $this->upsertSop($item);
            $this->bump($stats, $sop->wasRecentlyCreated);
        }

        return $stats;
    }

    /**
     * @param array<int, array<string, mixed>> $records
     *
     * @return array{imported: int, created: int, updated: int}
     */
    public function importKnowledge(array $records): array
    {
        $stats = ['imported' => 0, 'created' => 0, 'updated' => 0];

        foreach ($records as $item) {
            $knowledge = $this->upsertKnowledge($item);
            $this->bump($stats, $knowledge->wasRecentlyCreated);
        }

        return $stats;
    }

    public function upsertSop(array $item): Sop
    {
        $featuredImage = $this->optionalValue($item, 'featured_image');
        $status = $item['status'] ?? ResourceStatus::DRAFT->value;

        $sop = Sop::query()->updateOrCreate(
            ['slug' => $item['slug'] ?? Str::slug((string) $item['name'])],
            [
                'name' => (string) $item['name'],
                'short_description' => $this->optionalValue($item, 'short_description'),
                'content' => (string) ($item['content'] ?? ''),
                'principle' => $this->optionalValue($item, 'principle'),
                'department' => $this->optionalValue($item, 'department'),
                'version' => $this->optionalValue($item, 'version'),
                'author_name' => $this->optionalValue($item, 'author_name'),
                'reviewed_date' => $this->optionalValue($item, 'reviewed_date'),
                'last_verified_at' => $this->optionalValue($item, 'last_verified_date'),
                'mandatory_disclaimer' => $this->optionalValue($item, 'mandatory_disclaimer'),
                'featured_image' => $featuredImage,
                'status' => $status,
                'published_at' => $status === ResourceStatus::PUBLISHED->value ? now() : null,
                'seo_title' => $this->optionalValue($item, 'seo_title'),
                'seo_description' => $this->optionalValue($item, 'seo_description'),
                'focus_keyword' => $this->optionalValue($item, 'focus_keyword'),
                'reviewer_name' => $this->optionalValue($item, 'reviewed_by'),
                'reviewer_credentials' => $this->optionalValue($item, 'reviewer_credentials'),
                'last_reviewed_at' => $this->optionalValue($item, 'last_reviewed_date'),
                'faq' => $this->optionalValue($item, 'faqs', 'faq'),
            ],
        );

        $this->syncCategories($sop, $item['categories'] ?? []);
        $this->syncTags($sop, $item['tags'] ?? []);

        return $sop;
    }

    public function upsertKnowledge(array $item): KnowledgeResource
    {
        $featuredImage = $this->optionalValue($item, 'featuredImage', 'featured_image', 'image_url');
        $status = $item['status'] ?? ResourceStatus::DRAFT->value;

        $knowledge = KnowledgeResource::query()->updateOrCreate(
            ['slug' => $item['slug'] ?? Str::slug((string) $item['name'])],
            [
                'name' => (string) $item['name'],
                'short_description' => $this->optionalValue($item, 'shortDescription', 'short_description'),
                'content' => (string) ($item['content'] ?? ''),
                'topic_category' => $this->optionalValue($item, 'topicCategory', 'topic_category'),
                'difficulty_level' => $this->normalizeDifficulty($this->optionalValue($item, 'difficultyLevel', 'difficulty_level')),
                'learning_objectives' => $this->formatObjectives($this->optionalValue($item, 'learningObjectives', 'learning_objectives')),
                'author_name' => $this->optionalValue($item, 'authorName', 'author_name'),
                'featured_image' => $featuredImage,
                'reading_time_minutes' => $this->optionalValue($item, 'readingTimeMinutes', 'reading_time_minutes'),
                'status' => $status,
                'published_at' => $status === ResourceStatus::PUBLISHED->value ? now() : null,
                'seo_title' => $this->optionalValue($item, 'seoTitle', 'seo_title'),
                'seo_description' => $this->optionalValue($item, 'seoDescription', 'seo_description'),
            ],
        );

        $this->syncCategories($knowledge, $item['categories'] ?? []);
        $this->syncTags($knowledge, $item['tags'] ?? []);

        return $knowledge;
    }

    /**
     * @param array{imported: int, created: int, updated: int} $stats
     */
    private function bump(array &$stats, bool $wasRecentlyCreated): void
    {
        $stats['imported']++;
        $stats[$wasRecentlyCreated ? 'created' : 'updated']++;
    }

    /**
     * Turn a placeholder featured image into null so the public site never
     * tries to load a fake path.
     */
    private function optionalValue(array $item, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $item) && $item[$key] !== null) {
                $value = $item[$key];

                if (is_string($value) && in_array(trim($value), self::UNRESOLVED_IMAGE_MARKERS, true)) {
                    return null;
                }

                return $value;
            }
        }

        return null;
    }

    private function normalizeDifficulty(mixed $level): ?string
    {
        if ($level === null || $level === '') {
            return null;
        }

        $key = Str::lower((string) $level);

        return self::DIFFICULTY_LEVELS[$key] ?? (string) $level;
    }

    private function formatObjectives(mixed $objectives): ?string
    {
        if (is_array($objectives) && $objectives !== []) {
            return "By the end of this resource you should be able to:\n\n- ".implode("\n- ", $objectives);
        }

        return $objectives !== null && $objectives !== '' ? (string) $objectives : null;
    }

    private function syncCategories($resource, mixed $names): void
    {
        foreach ((array) $names as $categoryName) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug((string) $categoryName)],
                ['name' => (string) $categoryName, 'sort_order' => 0, 'is_active' => true]
            );

            $resource->categories()->syncWithoutDetaching([$category->id]);
        }
    }

    private function syncTags($resource, mixed $names): void
    {
        foreach ((array) $names as $tagName) {
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug((string) $tagName)],
                ['name' => (string) $tagName]
            );

            $resource->tags()->syncWithoutDetaching([$tag->id]);
        }
    }
}