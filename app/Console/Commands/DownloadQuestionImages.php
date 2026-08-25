<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\ImageDownloaderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadQuestionImages extends Command
{
    protected $signature = 'questions:download-images';

    protected $description = 'Download remote question images to local storage';

    public function handle(ImageDownloaderService $downloader): int
    {
        $downloaded = 0;
        $failed = 0;

        foreach (Question::whereNotNull('image_url')->get() as $question) {
            $dest = $question->image_path;

            if ($dest && Storage::disk('public')->exists($dest)) {
                continue;
            }

            $dest = $dest ?: 'questions/'.Str::slug($question->stem ?: 'question-'.$question->id).'.jpg';

            if ($downloader->downloadAndStore((string) $question->image_url, $dest)) {
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
