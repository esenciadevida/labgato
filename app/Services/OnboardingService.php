<?php

namespace App\Services;

use App\Models\RecentlyViewed;
use App\Models\User;

class OnboardingService
{
    public const TOUR_MEMBER_DASHBOARD = 'member-dashboard';

    public function checklistFor(User $user): array
    {
        $viewed = RecentlyViewed::query()
            ->where('user_id', $user->id)
            ->pluck('resource_type')
            ->unique();

        return [
            [
                'id' => 'profile',
                'label' => 'Complete your profile',
                'description' => 'Add a short bio so your contributions carry your expertise.',
                'href' => route('account.profile'),
                'done' => filled(trim((string) $user->bio)),
            ],
            [
                'id' => 'explore-sop',
                'label' => 'Explore an SOP',
                'description' => 'Browse step-by-step standard operating procedures.',
                'href' => route('public.sops.index'),
                'done' => $viewed->contains('sop'),
            ],
            [
                'id' => 'explore-knowledge',
                'label' => 'Read a knowledge article',
                'description' => 'Dig into curated laboratory knowledge resources.',
                'href' => route('public.knowledge.index'),
                'done' => $viewed->contains('knowledge'),
            ],
            [
                'id' => 'try-calculator',
                'label' => 'Try a calculator',
                'description' => 'Run a clinical calculation or check reference ranges.',
                'href' => route('public.calculators.index'),
                'done' => $viewed->contains('calculator'),
            ],
            [
                'id' => 'contribute',
                'label' => 'Make your first contribution',
                'description' => 'Share an SOP, knowledge resource, or interpretation.',
                'href' => route('account.contributions.create', ['type' => 'sop']),
                'done' => $user->sops()->exists()
                    || $user->knowledgeResources()->exists()
                    || $user->interpretationResources()->exists(),
            ],
        ];
    }

    public function dashboardTourSteps(): array
    {
        return [
            [
                'element' => '[data-tour="navigation"]',
                'title' => 'Navigate your hub',
                'description' => 'Switch between your dashboard, contributions, and account settings from these tabs.',
                'side' => 'bottom',
            ],
            [
                'element' => '[data-tour="submit"]',
                'title' => 'Share your knowledge',
                'description' => 'Start a new SOP, knowledge resource, or result interpretation here.',
                'side' => 'bottom',
            ],
            [
                'element' => '[data-tour="stats"]',
                'title' => 'Track your progress',
                'description' => 'See how many of your submissions are drafted, in review, published, or need changes.',
                'side' => 'bottom',
            ],
            [
                'element' => '[data-tour="recent"]',
                'title' => 'Manage submissions',
                'description' => 'Open any contribution to continue editing or review reviewer feedback.',
                'side' => 'top',
            ],
        ];
    }
}
