<?php

namespace App\Console\Commands;

use App\Support\SitemapGenerator;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the XML sitemap for public pages';

    public function handle(): int
    {
        $generated = SitemapGenerator::generateDefault();

        if ($generated) {
            $this->info('Sitemap generated successfully at public/sitemap.xml');

            return Command::SUCCESS;
        }

        $this->error('Failed to generate sitemap.');

        return Command::FAILURE;
    }
}
