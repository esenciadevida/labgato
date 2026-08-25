<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class QualityReport extends Command
{
    protected $signature = 'questions:quality-report';

    protected $description = 'Score every active question for ASCP-style quality and list the weakest items';

    private const VIGNETTE = ['patient', 'result', 'results', 'specimen', 'mg/dl', 'mmol', 'x10', 'smear', 'stain', 'culture', 'value', 'years'];

    public function handle(): int
    {
        $total = 0;
        $buckets = ['low' => 0, 'medium' => 0, 'high' => 0];

        $rows = [];

        foreach (Question::with('options')->where('status', 'active')->get() as $q) {
            $total++;

            $stemWords = count(preg_split('/\s+/', trim((string) $q->stem)));
            $combinedWords = $stemWords + count(preg_split('/\s+/', trim((string) ($q->case_text ?? ''))));

            $score = 0;
            $flags = [];

            if ($combinedWords >= 30) { $score += 25; }
            elseif ($combinedWords >= 18) { $score += 15; }
            else { $score += 5; $flags[] = 'short-stem(recal)'; }

            $hasCase = filled($q->case_text);
            if ($hasCase) { $score += 15; }

            $context = false;
            foreach (self::VIGNETTE as $k) {
                if (stripos((string) $q->stem, $k) !== false) { $context = true; break; }
            }
            if ($context) { $score += 10; }
            else { $flags[] = 'no-clinical-context'; }

            if (filled($q->explanation)) { $score += 15; }
            else { $flags[] = 'no-explanation'; }

            if ($q->usesImage()) { $score += 5; }

            $optLengths = $q->options->map(fn ($o) => count(preg_split('/\s+/', trim((string) $o->text))))->filter();
            $minLen = $optLengths->min() ?? 0;
            if ($optLengths->count() >= 3 && $minLen >= 2) { $score += 15; }
            else { $flags[] = 'weak-short-options'; }

            if ($optLengths->count() < 4) { $flags[] = 'few-options'; }

            if (filled($q->difficulty)) { $score += 10; }

            $score = min(100, $score);
            $bucket = $score >= 70 ? 'high' : ($score >= 45 ? 'medium' : 'low');
            $buckets[$bucket]++;

            $rows[] = [
                'id' => $q->id,
                'score' => $score,
                'bucket' => $bucket,
                'stem' => Str::limit($q->stem, 70),
                'flags' => implode(', ', $flags),
                'area' => $q->category?->name ?? '?',
            ];
        }

        $this->line(sprintf('Total: %d  |  LOW <50: %d  |  MED 50-74: %d  |  HIGH >=75: %d', $total, $buckets['low'], $buckets['medium'], $buckets['high']));
        $this->line('');

        $this->line('=== LOWEST-SCORING ITEMS (upgrade targets) ===');
        foreach (collect($rows)->sortBy('score')->take(40) as $r) {
            $this->line(sprintf("  #%-4d %2d [%s] %s  (%s)", $r['id'], $r['score'], $r['area'], $r['stem'], $r['flags'] ?: 'ok'));
        }

        return self::SUCCESS;
    }
}
