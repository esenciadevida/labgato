<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use Illuminate\Console\Command;

class QuestionJsonSeeder extends Command
{
    protected $signature = 'questions:seed {file? : Optional JSON file path, else all files in database/data/questions/}';

    protected $description = 'Seed questions from JSON files in database/data/questions/';

    public function handle(): int
    {
        $files = $this->resolveFiles();

        $imported = 0;
        $filesSeeded = 0;

        foreach ($files as $file) {
            if (! is_file($file)) {
                $this->warn('File not found: '.$file);

                continue;
            }

            $items = json_decode((string) file_get_contents($file), true);

            if (! is_array($items)) {
                throw new \RuntimeException('Invalid JSON structure in '.basename($file));
            }

            foreach ($items as $index => $item) {
                if (! is_array($item) || ! isset($item['stem'])) {
                    throw new \RuntimeException('Invalid question at index '.$index.' in '.basename($file));
                }

                if (Question::where('stem', $item['stem'])->exists()) {
                    $this->line('Skipped (already exists): '.mb_substr((string) $item['stem'], 0, 50).'...');

                    continue;
                }

                $this->createQuestion($item);
                $imported++;
                $this->info('Created: '.mb_substr((string) $item['stem'], 0, 50).'...');
            }

            $filesSeeded++;
        }

        $this->info('Imported '.$imported.' questions from '.$filesSeeded.' files.');

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function resolveFiles(): array
    {
        if ($file = $this->argument('file')) {
            return [base_path($file)];
        }

        return glob(database_path('data/questions/*.json')) ?: [];
    }

    private function createQuestion(array $item): Question
    {
        $category = QuestionCategory::firstOrCreate(
            ['name' => $item['category']],
            ['slug' => str($item['category'])->slug(), 'is_active' => true]
        );

        $question = Question::create([
            'question_category_id' => $category->id,
            'question_type' => $item['type'] ?? 'text',
            'stem' => $item['stem'],
            'case_text' => $item['case_text'] ?? null,
            'image_path' => $item['image'] ?? null,
            'image_alt' => $item['image_alt'] ?? null,
            'image_source' => $item['image_source'] ?? null,
            'image_license' => $item['image_license'] ?? null,
            'image_attribution' => $item['image_attribution'] ?? null,
            'image_source_url' => $item['image_url'] ?? null,
            'image_search' => $item['image_search'] ?? null,
            'image_spec' => $item['image_spec'] ?? null,
            'difficulty' => $item['difficulty'] ?? 'intermediate',
            'explanation' => $item['explanation'] ?? null,
            'reference' => $item['reference'] ?? null,
            'status' => $item['status'] ?? 'draft',
        ]);

        $correctPosition = $this->correctPosition((string) ($item['correct'] ?? 'A'));

        foreach (array_values($item['options'] ?? []) as $i => $text) {
            QuestionOption::create([
                'question_id' => $question->id,
                'label' => chr(65 + $i),
                'text' => $text,
                'is_correct' => $i === $correctPosition,
                'sort_order' => $i,
            ]);
        }

        return $question;
    }

    private function correctPosition(string $key): int
    {
        $letter = strtoupper(trim($key));

        if (preg_match('/^[A-Z]$/', $letter)) {
            return ord($letter) - 65;
        }

        return 0;
    }
}
