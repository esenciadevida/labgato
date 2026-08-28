<?php

namespace App\Console\Commands;

use App\Models\ContentImage;
use App\Services\ContentImageService;
use Illuminate\Console\Command;

class GenerateContentImageVariantsCommand extends Command
{
    protected $signature = 'content-images:generate-variants
        {--force : Regenerate variants even when one already exists}';

    protected $description = 'Generate responsive WebP variants for existing content images';

    public function handle(): int
    {
        $service = app(ContentImageService::class);
        $force = (bool) $this->option('force');

        $total = 0;
        $variants = 0;
        $failed = 0;

        ContentImage::query()
            ->where('path', 'like', 'content/%')
            ->where('path', 'like', '%.webp')
            ->whereNotNull('width')
            ->where('width', '>', 0)
            ->orderBy('id')
            ->chunkById(200, function ($images) use ($service, $force, &$total, &$variants, &$failed) {
                foreach ($images as $image) {
                    $total++;

                    try {
                        $written = $service->generateVariantsForPath($image->path, $force);
                        $variants += count($written);
                    } catch (\Throwable $e) {
                        $failed++;

                        $this->error(sprintf('Failed %s: %s', $image->path, $e->getMessage()));
                    }
                }

                $this->info(sprintf(
                    'Processed %d images (%d variants written, %d failed)...',
                    $total,
                    $variants,
                    $failed,
                ));
            });

        $this->info(sprintf('Done. Scanned %d images, wrote %d variants, %d failed.', $total, $variants, $failed));

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}