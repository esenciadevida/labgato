<?php

namespace App\Console\Commands;

use App\Services\MasterContentImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use JsonException;

class MasterContentImport extends Command
{
    protected $signature = 'master-content:import {file} {--type=sops : sops|knowledge}';

    protected $description = 'Import master SOP or knowledge content from a JSON dump file (idempotent, safe in production)';

    public function handle(MasterContentImporter $importer): int
    {
        $type = strtolower((string) $this->option('type'));
        $type = rtrim($type, 's') === 'sop' ? 'sops' : $type;

        if (! in_array($type, ['sops', 'knowledge'], true)) {
            $this->error("Unsupported type \"{$type}\". Use --type=sops or --type=knowledge.");

            return Command::FAILURE;
        }

        $path = (string) $this->argument('file');
        if (! File::exists($path) && File::exists(base_path($path))) {
            $path = base_path($path);
        }

        if (! File::exists($path)) {
            $this->error("File not found: {$path}");

            return Command::FAILURE;
        }

        try {
            $records = json_decode((string) File::get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->error("Invalid JSON in {$path}: ".$e->getMessage());

            return Command::FAILURE;
        }

        if (! is_array($records)) {
            $this->error("JSON in {$path} is not an array of records.");

            return Command::FAILURE;
        }

        $stats = $type === 'sops'
            ? $importer->importSops($records)
            : $importer->importKnowledge($records);

        $this->info(sprintf(
            'Imported %d %s: %d created, %d updated.',
            $stats['imported'],
            $type,
            $stats['created'],
            $stats['updated'],
        ));

        return Command::SUCCESS;
    }
}