<?php

namespace Database\Seeders;

use App\Models\KnowledgeResource;
use App\Services\MasterContentImporter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MasterKnowledgeSeeder extends Seeder
{
    private const SEED_FILES = [
        'knowledge_01_general_laboratory_upgraded.json',
        'knowledge_02_hematology.json',
        'knowledge_03_laboratory_knowledge.json',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $importer = app(MasterContentImporter::class);

        KnowledgeResource::query()->delete();

        $total = 0;

        foreach (self::SEED_FILES as $file) {
            $path = base_path('dump/'.$file);

            if (! File::exists($path)) {
                $this->command->warn("Knowledge seed file not found: {$path}");

                continue;
            }

            $data = json_decode(File::get($path), true);
            $records = $data['records'] ?? $data ?? [];
            $importer->importKnowledge($records);
            $total += count($records);

            $this->command->info(sprintf('Seeded %d knowledge records from %s.', count($records), $file));
        }

        $this->command->info("Knowledge seeding complete. Total records: {$total}.");
    }
}