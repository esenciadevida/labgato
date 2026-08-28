<?php

namespace App\Console\Commands;

use App\Models\ContentImage;
use App\Services\ContentImageService;
use Illuminate\Console\Command;

class PruneContentImagesCommand extends Command
{
    protected $signature = 'content-images:prune {--days=7 : Delete unattached images older than this many days}';

    protected $description = 'Delete uploaded content images that were never referenced by saved content';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));

        $images = ContentImage::query()
            ->whereNull('attached_at')
            ->where('created_at', '<', now()->subDays($days))
            ->get();

        if ($images->isEmpty()) {
            $this->info('No unattached content images to prune.');

            return self::SUCCESS;
        }

        $freedBytes = 0;
        $service = app(ContentImageService::class);

        foreach ($images as $image) {
            $service->deleteWithVariants($image->path);

            $freedBytes += (int) $image->size_bytes;
            $image->delete();
        }

        $this->info(sprintf(
            'Pruned %d unattached image(s), freeing %s.',
            $images->count(),
            number_format($freedBytes / 1024, 1).' KB',
        ));

        return self::SUCCESS;
    }
}
