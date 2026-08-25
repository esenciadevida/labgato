<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\QuestionCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AscpAudit extends Command
{
    protected $signature = 'questions:ascp-audit';

    protected $description = 'Audit the question bank against ASCP MLS content-area weights and item-quality flags';

    private const AREAS = [
        'Hematology' => ['slugs' => ['hematology'], 'range' => [20, 25], 'note' => null],
        'Chemistry' => ['slugs' => ['clinical-chemistry'], 'range' => [20, 25], 'note' => null],
        'Microbiology' => ['slugs' => ['microbiology'], 'range' => [17, 22], 'note' => null],
        'Blood Banking' => ['slugs' => ['blood-banking-transfusion'], 'range' => [10, 15], 'note' => null],
        'Immunology' => ['slugs' => ['immunology'], 'range' => [10, 15], 'note' => null],
        'Urinalysis & Body Fluids' => ['slugs' => ['urinalysis-body-fluids'], 'range' => [5, 10], 'note' => null],
        'Laboratory Operations' => ['slugs' => ['laboratory-operations'], 'range' => [5, 10], 'note' => 'merged Quality Control + Biosafety + lab pre-analytic/safety/math'],
        'Molecular Diagnostics' => ['slugs' => ['molecular-diagnostics'], 'range' => null, 'note' => 'not a distinct ASCP content area (cross-cutting)'],
    ];

    private const STEM_FLAGS = [
        'all of the above' => 'all-of-the-above',
        'all of these' => 'all-of-the-above',
        'none of the above' => 'none-of-the-above',
        'none of these' => 'none-of-the-above',
        'except' => 'negative-stem',
        'excepted' => 'negative-stem',
        'not true' => 'negative-stem',
        'false' => 'negative-stem',
        'incorrect' => 'negative-stem',
    ];

    public function handle(): int
    {
        $bySlug = QuestionCategory::pluck('name', 'slug');
        $idBySlug = QuestionCategory::pluck('id', 'slug');

        $this->line('=== COMPOSITION BY ASCP CONTENT AREA (active) ===');

        $activeTotal = Question::where('status', 'active')->count();

        foreach (self::AREAS as $area => $cfg) {
            $active = 0;
            foreach ($cfg['slugs'] as $slug) {
                $active += Question::where('question_category_id', $idBySlug[$slug] ?? 0)->where('status', 'active')->count();
            }

            $pct = $activeTotal > 0 ? round($active / $activeTotal * 100, 1) : 0;
            $range = $cfg['range'];
            $flag = $range === null ? 'n/a' : ($pct < $range[0] ? 'UNDER' : ($pct > $range[1] ? 'OVER' : 'in range'));

            $cats = implode(' + ', array_map(fn ($slug) => $bySlug[$slug] ?? $slug, $cfg['slugs']));

            $this->line(sprintf("  %-32s %4d  %6.1f%%   ASCP %-8s %-12s %s",
                $area, $active, $pct,
                $range === null ? 'n/a' : $range[0].'-'.$range[1].'%',
                $flag,
                $cfg['note'] ? '-- '.$cfg['note'] : $cats
            ));
        }

        $this->line(sprintf("\nTotal active: %d", $activeTotal));

        $this->line("\n=== QUALITY RED-FLAG SCAN (active) ===");

        $flagged = 0;
        foreach (Question::with('options')->where('status', 'active')->get() as $q) {
            $hits = [];

            foreach (self::STEM_FLAGS as $needle => $flag) {
                if (stripos((string) $q->stem, $needle) !== false) {
                    $hits[$flag] = true;
                }
            }

            foreach ($q->options as $opt) {
                $t = strtolower((string) $opt->text);
                if (str_contains($t, 'above') || str_contains($t, 'of these')) {
                    $hits['option-above/these'] = true;
                }
            }

            if ($hits) {
                $flagged++;
                $this->line(sprintf("  #%d [%s] %s", $q->id, implode(', ', array_keys($hits)), Str::limit($q->stem, 75)));
            }
        }

        $this->line(sprintf("Flagged: %d / %d", $flagged, $activeTotal));

        return self::SUCCESS;
    }
}
