<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\ImageDownloaderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DownloadQuestionImages extends Command
{
    protected $signature = 'questions:download-images';

    protected $description = 'Download remote question images to local storage';

    public function handle(ImageDownloaderService $downloader): int
    {
        $downloaded = 0;
        $failed = 0;

        $questions = Question::query()
            ->whereIn('question_type', ['image', 'text_image'])
            ->whereNotNull('image_source_url')
            ->get();

        foreach ($questions as $question) {
            $dest = $question->image_path;

            if ($dest && Storage::disk('public')->exists($dest)) {
                continue;
            }

            $dest = $dest ?: 'questions/'.$question->id;

            if ($downloader->downloadAndStore((string) $question->image_source_url, $dest)) {
                $question->update(['image_path' => $dest]);
                $downloaded++;
                $this->info('Downloaded: '.mb_substr($question->stem, 0, 50).'...');
            } else {
                $failed++;
                $this->warn('Failed: '.mb_substr($question->stem, 0, 50).'...');
            }
        }

        $this->info('Downloaded '.$downloaded.' images, failed '.$failed.'.');

        return self::SUCCESS;
    }
}
