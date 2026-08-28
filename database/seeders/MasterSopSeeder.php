<?php

namespace Database\Seeders;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\Sop;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MasterSopSeeder extends Seeder
{
    private const SEED_FILES = [
        'labgoto_hematology_sops.json',
    ];

    /** Placeholder values mean the featured image has not been finalized yet. */
    private const UNRESOLVED_IMAGE_MARKERS = ['', 'IMAGE_URL_TO_BE_CONFIRMED'];

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        Sop::query()->delete();

        $total = 0;

        foreach (self::SEED_FILES as $file) {
            $path = base_path('dump/'.$file);

            if (! File::exists($path)) {
                $this->command->warn("SOP seed file not found: {$path}");

                continue;
            }

            $records = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR) ?? [];

            foreach ($records as $item) {
                $featuredImage = $item['featured_image'] ?? '';
                if (in_array($featuredImage, self::UNRESOLVED_IMAGE_MARKERS, true)) {
                    $featuredImage = null;
                }

                $status = $item['status'] ?? ResourceStatus::DRAFT->value;

                $sop = Sop::create([
                    'name' => $item['name'],
                    'slug' => $item['slug'] ?? Str::slug($item['name']),
                    'short_description' => $item['short_description'] ?? null,
                    'content' => $item['content'] ?? '',
                    'principle' => $item['principle'] ?? null,
                    'department' => $item['department'] ?? null,
                    'version' => $item['version'] ?? null,
                    'author_name' => $item['author_name'] ?? null,
                    'reviewed_date' => $item['reviewed_date'] ?? null,
                    'last_verified_at' => $item['last_verified_date'] ?? null,
                    'mandatory_disclaimer' => $item['mandatory_disclaimer'] ?? null,
                    'featured_image' => $featuredImage,
                    'status' => $status,
                    'published_at' => $status === ResourceStatus::PUBLISHED->value ? now() : null,
                    'seo_title' => $item['seo_title'] ?? null,
                    'seo_description' => $item['seo_description'] ?? null,
                    'focus_keyword' => $item['focus_keyword'] ?? null,
                    'reviewer_name' => $item['reviewed_by'] ?? null,
                    'reviewer_credentials' => $item['reviewer_credentials'] ?? null,
                    'last_reviewed_at' => $item['last_reviewed_date'] ?? null,
                    'faq' => $item['faqs'] ?? null,
                ]);

                foreach ($item['categories'] ?? [] as $categoryName) {
                    $category = Category::firstOrCreate(
                        ['slug' => Str::slug($categoryName)],
                        ['name' => $categoryName, 'sort_order' => 0, 'is_active' => true]
                    );

                    $sop->categories()->syncWithoutDetaching([$category->id]);
                }

                foreach ($item['tags'] ?? [] as $tagName) {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );

                    $sop->tags()->syncWithoutDetaching([$tag->id]);
                }

                $total++;
            }

            $this->command->info(sprintf('Seeded %d SOP records from %s.', count($records), $file));
        }

        $this->command->info("SOP seeding complete. Total records: {$total}.");
    }
}