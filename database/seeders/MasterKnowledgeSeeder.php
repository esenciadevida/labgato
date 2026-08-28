<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\KnowledgeResource;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MasterKnowledgeSeeder extends Seeder
{
    private const SEED_FILES = [
        'knowledge_01_general_laboratory_upgraded.json',
        'knowledge_02_hematology.json',
    ];

    private const DIFFICULTY_LEVELS = [
        'beginner' => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        KnowledgeResource::query()->delete();

        $total = 0;

        foreach (self::SEED_FILES as $file) {
            $path = base_path('dump/'.$file);

            if (! File::exists($path)) {
                $this->command->warn("Knowledge seed file not found: {$path}");

                continue;
            }

            $data = json_decode(File::get($path), true);
            $records = $data['records'] ?? [];

            foreach ($records as $item) {
                $knowledge = KnowledgeResource::create([
                    'name' => $item['name'],
                    'slug' => $item['slug'] ?? Str::slug($item['name']),
                    'short_description' => $item['shortDescription'] ?? null,
                    'content' => $item['content'] ?? '',
                    'topic_category' => $item['topicCategory'] ?? null,
                    'difficulty_level' => $this->normalizeDifficulty($item['difficultyLevel'] ?? null),
                    'learning_objectives' => $this->formatObjectives($item['learningObjectives'] ?? null),
                    'author_name' => $item['authorName'] ?? null,
                    'featured_image' => $item['featuredImage'] ?? $item['image_url'] ?? null,
                    'reading_time_minutes' => $item['readingTimeMinutes'] ?? null,
                    'status' => $item['status'] ?? 'draft',
                    'seo_title' => $item['seoTitle'] ?? null,
                    'seo_description' => $item['seoDescription'] ?? null,
                ]);

                foreach ($item['categories'] ?? [] as $categoryName) {
                    $category = Category::firstOrCreate(
                        ['slug' => Str::slug($categoryName)],
                        ['name' => $categoryName, 'sort_order' => 0, 'is_active' => true]
                    );

                    $knowledge->categories()->syncWithoutDetaching([$category->id]);
                }

                foreach ($item['tags'] ?? [] as $tagName) {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );

                    $knowledge->tags()->syncWithoutDetaching([$tag->id]);
                }

                $total++;
            }

            $this->command->info(sprintf('Seeded %d knowledge records from %s.', count($records), $file));
        }

        $this->command->info("Knowledge seeding complete. Total records: {$total}.");
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
}
