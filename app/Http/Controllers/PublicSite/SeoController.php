<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Support\SitemapGenerator;

class SeoController extends Controller
{
    public function robots()
    {
        return response(view('robots'))
            ->header('Content-Type', 'text/plain');
    }

    public function sitemap()
    {
        $path = public_path('sitemap.xml');

        if (! is_file($path)) {
            try {
                SitemapGenerator::generateDefault();
            } catch (\Throwable) {
                abort(500);
            }
        }

        if (! is_file($path)) {
            abort(404);
        }

        return response(file_get_contents($path))
            ->header('Content-Type', 'application/xml');
    }
}
