<?php

namespace App\Console\Commands;

use App\Models\ContentImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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

        $disk = Storage::disk('public');
        $freedBytes = 0;

        foreach ($images as $image) {
            if ($disk->exists($image->path)) {
                $disk->delete($image->path);
            }

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
