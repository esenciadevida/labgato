<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class QuestionImportService
{
    public const COLUMNS = [
        'category', 'type', 'difficulty', 'stem',
        'option_a', 'option_b', 'option_c', 'option_d',
        'correct_option', 'explanation',
    ];

    /**
     * Extra columns accepted when present but not required, so older CSVs
     * built from the original template keep importing unchanged.
     */
    public const OPTIONAL_COLUMNS = [
        'image_url', 'image_alt',
    ];

    private const LETTERS = ['a', 'b', 'c', 'd'];

    private const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    /**
     * Parse a CSV file and validate every row.
     *
     * @return array{rows: array, valid: array, invalid: array, header_error: ?string}
     */
    public function parse(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return ['rows' => [], 'valid' => [], 'invalid' => [], 'header_error' => 'The CSV file could not be opened.'];
        }

        $headers = fgetcsv($handle, 0, ',');

        if ($headers === false || $headers === [null]) {
            fclose($handle);

            return ['rows' => [], 'valid' => [], 'invalid' => [], 'header_error' => 'The CSV file is empty or could not be read.'];
        }

        $normalized = [];
        foreach ($headers as $index => $header) {
            $header = strtolower(trim((string) $header));

            if ($index === 0) {
                $header = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', $header)));
            }

            $normalized[$index] = $header;
        }

        $missing = array_diff(self::COLUMNS, $normalized);

        if (! empty($missing)) {
            fclose($handle);

            return [
                'rows' => [],
                'valid' => [],
                'invalid' => [],
                'header_error' => 'Required column(s) missing: '.implode(', ', $missing).'. Please use the provided template.',
            ];
        }

        // Optional columns are picked up when the header includes them.
        foreach (self::OPTIONAL_COLUMNS as $optional) {
            if (! in_array($optional, $normalized, true)) {
                $normalized[] = $optional;
            }
        }

        $categories = QuestionCategory::query()->get(['id', 'name']);
        $rows = [];

        $line = 1;

        while (($raw = fgetcsv($handle, 0, ',')) !== false) {
            $line++;

            if ($raw === [null] || count(array_filter($raw, fn ($cell) => trim((string) $cell) !== '')) === 0) {
                continue;
            }

            $data = [];

            foreach (array_merge(self::COLUMNS, self::OPTIONAL_COLUMNS) as $column) {
                $index = array_search($column, $normalized, true);
                $value = isset($raw[$index]) ? trim((string) $raw[$index]) : '';
                $data[$column] = $value === '' ? null : $value;
            }

            [$valid, $errors, $normalizedRow] = $this->validateRow($data, $line, $categories);

            $rows[] = [
                'line' => $line,
                'valid' => $valid,
                'errors' => $errors,
                'data' => $normalizedRow,
            ];

            if ($valid) {
                $rows[array_key_last($rows)]['import'] = $normalizedRow;
            }
        }

        fclose($handle);

        $validRows = [];

        foreach ($rows as $row) {
            if ($row['valid']) {
                $validRows[$row['line']] = $row['import'];
            }
        }

        return [
            'rows' => $rows,
            'valid' => $validRows,
            'invalid' => array_values(array_filter($rows, fn ($row) => ! $row['valid'])),
            'header_error' => null,
        ];
    }

    /**
     * Validate one row; returns [valid, errors[], normalized[]].
     */
    public function validateRow(array $data, int $lineNo, $categories = null): array
    {
        $errors = [];
        $categories ??= QuestionCategory::query()->get(['id', 'name']);

        $categoryName = (string) ($data['category'] ?? '');

        $category = $categories->first(
            fn ($item) => strcasecmp(trim($item->name), trim($categoryName)) === 0
        );

        if ($categoryName === '') {
            $errors[] = 'Subject is required.';
        } elseif ($category === null) {
            $errors[] = "Unknown subject \"{$categoryName}\".";
        }

        $type = strtolower((string) ($data['type'] ?? ''));

        if ($type === '') {
            $type = 'text';
        }

        $imageUrl = trim((string) ($data['image_url'] ?? ''));

        if (! in_array($type, Question::TYPES, true)) {
            $errors[] = "Invalid type \"{$type}\". Allowed: ".implode(', ', Question::TYPES).'.';
        }

        // Questions that carry an image must use an image-aware type, and the
        // reference must be a downloadable URL or a path on the public disk.
        $imageAlt = (string) ($data['image_alt'] ?? '');

        if ($imageUrl !== '') {
            if (in_array($type, ['calculation', 'case'], true)) {
                $errors[] = "Type \"{$type}\" cannot be combined with image_url. Use text_image or image instead.";
            } elseif (preg_match('#^https?://#i', $imageUrl) !== 1) {
                if (Storage::disk('public')->exists($imageUrl)) {
                    $type = $type === 'text' ? 'text_image' : $type;
                } else {
                    $errors[] = "Image \"{$imageUrl}\" is neither a valid http(s) URL nor a file in the media library.";
                }
            } else {
                $type = $type === 'text' ? 'text_image' : $type;
            }
        }

        $difficulty = strtolower((string) ($data['difficulty'] ?? ''));

        if ($difficulty === '') {
            $difficulty = 'intermediate';
        }

        if (! in_array($difficulty, Question::DIFFICULTIES, true)) {
            $errors[] = "Invalid difficulty \"{$difficulty}\". Allowed: ".implode(', ', Question::DIFFICULTIES).'.';
        }

        $stem = (string) ($data['stem'] ?? '');

        if (trim($stem) === '') {
            $errors[] = 'Stem is required.';
        }

        $options = [];

        foreach (self::LETTERS as $letter) {
            $text = (string) ($data['option_'.$letter] ?? '');

            if (trim($text) !== '') {
                $options[strtoupper($letter)] = trim($text);
            }
        }

        if (count($options) < 2) {
            $errors[] = 'At least two options (A and B minimum) are required.';
        }

        $correctLetter = strtoupper(trim((string) ($data['correct_option'] ?? '')));

        if ($correctLetter === '') {
            $errors[] = 'Correct option letter is required.';
        } elseif (! in_array($correctLetter, array_map('strtoupper', self::LETTERS), true)) {
            $errors[] = "Invalid correct option \"{$correctLetter}\". Allowed: A, B, C, D.";
        } elseif (! array_key_exists($correctLetter, $options)) {
            $errors[] = "Correct option {$correctLetter} has no option text.";
        }

        if ($errors !== []) {
            return [false, $errors, $data];
        }

        $optionTexts = array_values($options);
        $letters = array_keys($options);

        $normalizedRow = [
            'question_category_id' => $category->id,
            'question_type' => $type,
            'difficulty' => $difficulty,
            'stem' => trim($stem),
            'explanation' => (string) ($data['explanation'] ?? '') !== '' ? trim((string) $data['explanation']) : null,
            'options' => $optionTexts,
            'correct_index' => array_search($correctLetter, $letters, true),
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'image_alt' => $imageAlt !== '' ? $imageAlt : null,
        ];

        return [true, [], $normalizedRow];
    }

    public function template(): string
    {
        $stream = fopen('php://temp', 'w+');

        fputcsv($stream, [...self::COLUMNS, ...self::OPTIONAL_COLUMNS]);

        fputcsv($stream, [
            'Hematology', 'text', 'easy',
            'Which cell lineage does the term "left shift" refer to?',
            'Neutrophil series', 'Lymphocyte series', 'Erythroid series', 'Megakaryocytic series',
            'A',
            'Left shift describes an increase in immature neutrophil precursors in the peripheral blood.',
            '', '',
        ]);

        fputcsv($stream, [
            'Clinical Chemistry', 'calculation', 'intermediate',
            'An anion gap of which range is considered normal using K+-free formula?',
            '3-11 mmol/L', '12-20 mmol/L', '22-28 mmol/L', '30-40 mmol/L',
            'A',
            '',
            '', '',
        ]);

        fputcsv($stream, [
            'Hematology', 'text_image', 'intermediate',
            'Identify the cell indicated by the arrow in the blood film.',
            'Band neutrophil', 'Metamyelocyte', 'Myeloblast', 'Promyelocyte',
            'B',
            'The cell shows a kidney-shaped nucleus characteristic of a metamyelocyte.',
            'https://example.com/blood-film-metamyelocyte.png',
            'Peripheral blood film showing a metamyelocyte',
        ]);

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return $content;
    }

    public function import(array $validRows, string $asStatus = 'draft'): array
    {
        $status = in_array($asStatus, ['draft', 'active'], true) ? $asStatus : 'draft';

        $imported = 0;
        $warnings = [];

        foreach ($validRows as $line => $row) {
            $question = Question::create([
                'question_category_id' => $row['question_category_id'],
                'question_type' => $row['question_type'],
                'difficulty' => $row['difficulty'],
                'stem' => $row['stem'],
                'explanation' => $row['explanation'],
                'status' => $status,
            ]);

            if (! empty($row['image_url'])) {
                $storedPath = $this->storeImportedImage($question, $row['image_url']);

                if ($storedPath !== null) {
                    $question->forceFill([
                        'image_path' => $storedPath,
                        'image_alt' => $row['image_alt'] ?? null,
                    ])->save();
                } else {
                    $warnings[] = "Line {$line}: image could not be fetched from {$row['image_url']}. The question was imported without it - add the image manually.";
                }
            }

            foreach ($row['options'] as $index => $text) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'label' => chr(65 + $index),
                    'text' => $text,
                    'is_correct' => $index === $row['correct_index'],
                    'sort_order' => $index,
                ]);
            }

            $imported++;
        }

        return ['imported' => $imported, 'status' => $status, 'warnings' => $warnings];
    }

    /**
     * Fetch an image for an imported question. Remote URLs are downloaded
     * into storage; values that already point at the public disk are used
     * as-is. Returns the stored path or null when the image is unusable.
     */
    private function storeImportedImage(Question $question, string $reference): ?string
    {
        if (preg_match('#^https?://#i', $reference) !== 1) {
            return Storage::disk('public')->exists($reference) ? $reference : null;
        }

        try {
            $response = Http::timeout(15)->maxRedirects(3)->get($reference);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $body = (string) $response->body();

        if ($body === '' || strlen($body) > 8 * 1024 * 1024) {
            return null;
        }

        $extension = strtolower(pathinfo(parse_url($reference, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            $extension = 'png';
        }

        $filename = uniqid('', true).'.'.$extension;
        $path = "questions/{$question->id}/{$filename}";

        return Storage::disk('public')->put($path, $body) ? $path : null;
    }
}
