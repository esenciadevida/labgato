<?php

namespace Database\Seeders;

use App\Models\Sop;
use App\Services\MasterContentImporter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MasterSopSeeder extends Seeder
{
    private const SEED_FILES = [
        'labgoto_hematology_sops.json',
        'labgoto_manual_cbc_sop.json',
        'labgoto_manual_reticulocyte_count_sop.json',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $importer = app(MasterContentImporter::class);

        Sop::query()->delete();

        $total = 0;

        foreach (self::SEED_FILES as $file) {
            $path = base_path('dump/'.$file);

            if (! File::exists($path)) {
                $this->command->warn("SOP seed file not found: {$path}");

                continue;
            }

            $records = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR) ?? [];
            $importer->importSops($records);
            $total += count($records);

            $this->command->info(sprintf('Seeded %d SOP records from %s.', count($records), $file));
        }

        $this->command->info("SOP seeding complete. Total records: {$total}.");
    }
}