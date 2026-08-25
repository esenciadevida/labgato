<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;

class UpgradeRecall extends Command
{
    protected $signature = 'questions:upgrade-recall';

    protected $description = 'Rewrite low-quality recall questions (not used in exams) into clinical vignettes';

    public function handle(): int
    {
        $path = database_path('data/upgrade-recall.json');
        $items = json_decode((string) file_get_contents($path), true);

        if (! is_array($items)) {
            $this->error('Could not parse upgrade-recall.json.');

            return self::FAILURE;
        }

        $upgraded = 0;

        foreach ($items as $item) {
            $question = Question::where('stem', 'like', '%'.$item['fragment'].'%')->first();

            if ($question === null || $question->usedInExams()) {
                continue;
            }

            $question->update([
                'stem' => $item['stem'],
                'case_text' => $item['case_text'],
                'question_type' => 'case',
                'explanation' => $item['explanation'],
            ]);

            $question->options()->delete();

            foreach (array_values($item['options']) as $index => $text) {
                $question->options()->create([
                    'label' => chr(65 + $index),
                    'text' => $text,
                    'is_correct' => chr(65 + $index) === $item['correct'],
                    'sort_order' => $index,
                ]);
            }

            $upgraded++;
            $this->line('Upgraded #'.$question->id.': '.substr((string) $item['stem'], 0, 55));
        }

        $this->info("Upgraded {$upgraded} question(s).");

        return self::SUCCESS;
    }
}
