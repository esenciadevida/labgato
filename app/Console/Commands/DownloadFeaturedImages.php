<?php

namespace App\Console\Commands;

use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\Sop;
use App\Services\ContentImageService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class DownloadFeaturedImages extends Command
{
    protected $signature = 'featured-images:download {--dry-run}';

    protected $description = 'Download external (http(s)) featured images to local storage for knowledge, SOP, and interpretation resources';

    public function handle(ContentImageService $images): int
    {
        $downloaded = 0;
        $skipped = 0;
        $failed = 0;

        foreach ([KnowledgeResource::class, Sop::class, InterpretationResource::class] as $model) {
            foreach ($model::query()->whereNotNull('featured_image')->get() as $resource) {
                $url = (string) $resource->featured_image;

                if (! preg_match('#^https?://#i', $url)) {
                    $skipped++;

                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->info('Would download: '.$resource->name);

                    continue;
                }

                try {
                    $tmp = $this->download($url);

                    $path = $images->store(new UploadedFile(
                        $tmp,
                        basename((string) parse_url($url, PHP_URL_PATH)) ?: 'featured.jpg',
                        (new \finfo(FILEINFO_MIME_TYPE))->file($tmp),
                        null,
                        true,
                    ));

                    $resource->update(['featured_image' => $path]);
                    $downloaded++;
                    $this->info('Downloaded: '.$resource->name);
                } catch (\Throwable $e) {
                    $failed++;
                    $this->warn('Failed: '.$resource->name.' - '.$e->getMessage());
                } finally {
                    if (isset($tmp) && is_file($tmp)) {
                        @unlink($tmp);
                    }
                }
            }
        }

        $this->info("Downloaded {$downloaded} featured images, skipped {$skipped}, failed {$failed}.");

        return self::SUCCESS;
    }

    private function download(string $url): string
    {
        $response = Http::withHeaders([
            'User-Agent' => 'LabGOTO/1.0 (https://labgoto.com; admin@labgoto.com) Laravel/'.app()->version(),
        ])->timeout(40)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("HTTP {$response->status()}");
        }

        $tmp = tempnam(sys_get_temp_dir(), 'featured_');

        if ($tmp === false) {
            throw new \RuntimeException('Could not create a temporary file.');
        }

        file_put_contents($tmp, $response->body());

        return $tmp;
    }
}