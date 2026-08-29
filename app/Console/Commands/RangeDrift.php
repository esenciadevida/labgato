<?php

namespace App\Console\Commands;

use App\Enums\ResourceStatus;
use App\Models\ReferenceRange;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RangeDrift extends Command
{
    protected $signature = 'ranges:drift';

    protected $description = 'Compare published reference ranges in the database against the canonical ReferenceRangeDataSeeder set';

    private const CATEGORY_SLUGS = [
        'hematology',
        'clinical-chemistry',
        'lipid-panel',
        'thyroid-function',
        'coagulation',
        'metabolic',
    ];

    public function handle(): int
    {
        $seederPath = database_path('seeders/ReferenceRangeDataSeeder.php');

        if (! File::exists($seederPath)) {
            $this->error('ReferenceRangeDataSeeder not found.');

            return self::FAILURE;
        }

        preg_match_all("/'slug' => '([^']+)'/", File::get($seederPath), $matches);

        $canonical = array_values(array_filter(
            $matches[1],
            fn (string $slug) => ! in_array($slug, self::CATEGORY_SLUGS, true)
        ));

        $dbSlugs = ReferenceRange::query()
            ->where('status', ResourceStatus::PUBLISHED->value)
            ->pluck('slug')
            ->all();

        $missing = array_values(array_diff($canonical, $dbSlugs));
        $extra = array_values(array_diff($dbSlugs, $canonical));

        $this->newLine();
        $this->info('Canonical records (seeder): '.count($canonical));
        $this->line('Records in database:        '.count($dbSlugs));
        $this->newLine();

        $drift = false;

        if ($missing !== []) {
            $drift = true;
            $this->line('  [!] Canonical range(s) missing from database:');
            foreach ($missing as $slug) {
                $this->line('      - '.$slug);
            }
        }

        if ($extra !== []) {
            $drift = true;
            $this->line('  [!] Published database range(s) not in the canonical set:');

            foreach ($extra as $slug) {
                $this->line('      - '.$slug);
            }
        }

        $archived = ReferenceRange::query()
            ->whereNot('status', ResourceStatus::PUBLISHED->value)
            ->pluck('slug')
            ->sort()
            ->values()
            ->all();

        if ($archived !== []) {
            $this->line('Archived/non-published spares (ignored): '.implode(', ', $archived));
        }

        if (! $drift) {
            $this->info('No drift: database matches the canonical seeder set.');
        }

        return $drift ? self::FAILURE : self::SUCCESS;
    }
}