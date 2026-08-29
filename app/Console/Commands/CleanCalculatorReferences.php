<?php

namespace App\Console\Commands;

use App\Models\Calculator;
use Illuminate\Console\Command;

class CleanCalculatorReferences extends Command
{
    protected $signature = 'calculator-references:clean';

    protected $description = 'Remove external range-authority links from calculator reference lists (idempotent)';

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
        $changed = 0;
        $removed = 0;

        foreach (Calculator::all() as $calculator) {
            $references = json_decode((string) $calculator->references, true);

            if (! is_array($references)) {
                continue;
            }

            $filtered = array_values(array_filter($references, function ($reference) use (&$removed) {
                if (! is_array($reference) || ! isset($reference['url'])) {
                    return true;
                }

                $host = strtolower((string) parse_url($reference['url'], PHP_URL_HOST));

                if (in_array($host, self::RANGE_AUTHORITY_HOSTS, true)) {
                    $removed++;
                    $this->line('  [remove] '.$reference['url']);

                    return false;
                }

                return true;
            }));

            $encoded = json_encode($filtered, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($encoded !== (string) $calculator->references) {
                $calculator->forceFill(['references' => $encoded])->save();
                $changed++;
            }
        }

        $this->newLine();
        $this->info("Calculators updated: {$changed}");
        $this->info("Range-authority links removed: {$removed}");

        return self::SUCCESS;
    }
}
