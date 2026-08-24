<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\LaunchSetting;
use App\Services\RecentlyViewedService;
use App\Services\RecommendationService;

class HomeController extends Controller
{
    public function __construct(
        private readonly RecentlyViewedService $recentlyViewed,
        private readonly RecommendationService $recommendations,
    ) {}

    public function index()
    {
        $pageTitle = 'Lab Calculators, Reference Ranges & SOPs';
        $metaDescription = 'Labgoto is a free medical laboratory knowledge platform with clinical calculators, reference ranges, SOPs, and lab result interpretation guides for medical laboratory scientists and laboratory professionals.';

        $recentlyViewed = collect();
        $recommended = collect();

        if (auth()->check()) {
            $recentlyViewed = $this->recentlyViewed->getRecent(auth()->user(), 8);
            $recommended = $this->recommendations->getRecommendedFor(auth()->user(), 6);
        }

        $assessmentsLive = LaunchSetting::current()->isLive();
        $flagship = $assessmentsLive
            ? Assessment::query()->open()->challenges()->orderBy('closes_at')->first()
                ?? Assessment::query()->open()->orderBy('closes_at')->first()
            : null;

        return view('public.home', compact(
            'pageTitle',
            'metaDescription',
            'recentlyViewed',
            'recommended',
            'assessmentsLive',
            'flagship',
        ));
    }

    public function clearRecentlyViewed()
    {
        $deleted = $this->recentlyViewed->clear(auth()->id());

        return back()->with('success', "Cleared {$deleted} recently viewed items.");
    }
}
