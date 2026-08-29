<?php

namespace App\Console\Commands;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\ReferenceRange;
use App\Models\Sop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RangeAudit extends Command
{
    protected $signature = 'ranges:audit';

    protected $description = 'Audit reference-range governance: SOP-linked records, calculator coverage, and stray hardcoded intervals';

    private const RANGE_AUTHORITY_HOSTS = [
        'reference.medscape.com',
        'www.labtestsguide.com',
        'labtestsonline.org',
        'www.labtestsonline.org',
        'www.medicalalgorithms.com',
        'www.medaptly.com',
        'www.meddean.luc.edu',
        'meddean.luc.edu',
    ];

    public function handle(): int
    {
        $dumpPath = base_path('dump');
        $dumps = File::files($dumpPath);

        $this->section('Canonical records');
        $total = ReferenceRange::query()->published()->count();
        $withSops = ReferenceRange::query()
            ->published()
            ->whereHas('sops')
            ->count();
        $this->line("Published reference ranges: {$total}");
        $this->line("  linked from at least one SOP: {$withSops}");

        $unlinked = ReferenceRange::query()
            ->published()
            ->whereDoesntHave('sops')
            ->get(['slug', 'analyte_name']);

        if ($unlinked->isNotEmpty()) {
            $this->newLine();
            $this->line('Ranges not linked from any SOP (informational):');
            foreach ($unlinked as $range) {
                $this->line('  - '.$range->analyte_name.' ('.$range->slug.')');
            }
        }

        $this->section('SOP dumps');
        $hardcoded = 0;

        foreach ($dumps as $file) {
            if (! str_contains($file->getFilename(), 'sop')) {
                continue;
            }

            $data = json_decode((string) $file->getContents(), true);
            $record = $data[0] ?? null;

            if (! is_array($record)) {
                continue;
            }

            $hasIntervalHeading = str_contains((string) ($record['content'] ?? ''), 'Reference Interval');
            $hasRangesKey = array_key_exists('reference_ranges', $record);

            if ($hasIntervalHeading && ! $hasRangesKey) {
                $hardcoded++;
                $this->line('  [!] hardcoded interval without reference_ranges key: '.$record['slug']);
            }
        }

        $this->line("Hardcoded-interval dumps missing links: {$hardcoded}");

        $this->section('Calculators');
        $canonicalKeys = ReferenceRange::query()
            ->published()
            ->whereNotNull('parameter_key')
            ->distinct()
            ->pluck('parameter_key')
            ->all();

        $missingKeys = 0;
        $externalLinks = [];

        foreach (Calculator::query()->published()->get(['id', 'slug', 'output_schema', 'references']) as $calculator) {
            foreach ($calculator->output_schema as $field) {
                $key = $field['key'] ?? null;

                if (in_array($key, $canonicalKeys, true) && empty($field['reference_key'])) {
                    $missingKeys++;
                    $this->line('  [!] no reference_key: '.$calculator->slug.' / '.$key);
                }
            }

            $references = json_decode((string) $calculator->references, true);

            if (is_array($references)) {
                foreach ($references as $reference) {
                    if (! is_array($reference) || ! isset($reference['url'])) {
                        continue;
                    }

                    $host = strtolower((string) parse_url($reference['url'], PHP_URL_HOST));

                    if (in_array($host, self::RANGE_AUTHORITY_HOSTS, true)) {
                        $externalLinks[] = $calculator->slug.' -> '.$reference['url'];
                    }
                }
            }
        }

        $this->line("Output fields left without reference_key: {$missingKeys}");
        $this->line('Range-authority links remaining: '.count($externalLinks));

        foreach ($externalLinks as $link) {
            $this->line('  [!] '.$link);
        }

        $this->section('SOP records in DB');
        $emptyPivotSops = Sop::query()
            ->where('status', ResourceStatus::PUBLISHED->value)
            ->whereDoesntHave('referenceRanges')
            ->count();
        $this->line("Published SOPs with no linked reference ranges: {$emptyPivotSops}");

        return self::SUCCESS;
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->info('== '.$title.' ==');
    }
}
