<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\LaunchSetting;
use App\Services\LeaderboardService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function __construct(private LeaderboardService $leaderboard)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user !== null && $user->isAdministrator();
        $globalAvailable = LaunchSetting::current()->leaderboardAvailable();

        $assessments = Assessment::query()
            ->whereHas('attempts', fn ($query) => $query
                ->where('mode', 'official')
                ->whereIn('status', ['submitted', 'auto_submitted']))
            ->orderByDesc('closes_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->filter(fn (Assessment $assessment) => $isAdmin || $this->leaderboard->isBoardVisible($assessment))
            ->values();

        $locked = ! $globalAvailable && ! $isAdmin;

        $allTime = collect();
        $totalParticipants = 0;
        $myPosition = null;
        $myRow = null;
        $paginated = null;
        $jumpPage = null;
        $onPage = false;

        if (! $locked) {
            $allTime = collect($this->leaderboard->allTime());
            $totalParticipants = $allTime->count();
            $myPosition = $this->leaderboard->position($allTime->all(), $user?->id);
            $myRow = $myPosition !== null ? $allTime->firstWhere('rank', $myPosition) : null;
            /** @var LengthAwarePaginator $paginated */
            $paginated = $this->paginate($allTime, LeaderboardService::PER_PAGE);
            $allTime = $paginated->getCollection();
            if ($myPosition !== null) {
                $jumpPage = $this->leaderboard->pageFor($myPosition);
                $onPage = $jumpPage === $paginated->currentPage();
            }
        }

        return view('public.leaderboard.index', [
            'assessments' => $assessments,
            'rows' => $allTime,
            'paginated' => $paginated,
            'locked' => $locked,
            'totalParticipants' => $totalParticipants,
            'myPosition' => $myPosition,
            'myRow' => $myRow,
            'jumpPage' => $jumpPage,
            'onPage' => $onPage,
            'variant' => 'alltime',
        ]);
    }

    public function show(Request $request, Assessment $assessment): View
    {
        $user = $request->user();
        $isAdmin = $user !== null && $user->isAdministrator();
        $visible = $this->leaderboard->isBoardVisible($assessment);

        if (! $visible && ! $isAdmin) {
            return view('public.leaderboard.show', [
                'assessment' => $assessment,
                'rows' => collect(),
                'paginated' => null,
                'locked' => true,
                'totalParticipants' => 0,
                'myPosition' => null,
                'myRow' => null,
                'jumpPage' => null,
                'onPage' => false,
                'variant' => 'assessment',
            ]);
        }

        $board = collect($this->leaderboard->board($assessment));
        $myPosition = $this->leaderboard->position($board->all(), $user?->id);
        $jumpPage = $myPosition !== null ? $this->leaderboard->pageFor($myPosition) : null;

        /** @var LengthAwarePaginator $paginated */
        $paginated = $this->paginate($board, LeaderboardService::PER_PAGE);
        $pageRows = $paginated->getCollection();

        $myRow = $myPosition !== null ? $board->firstWhere('rank', $myPosition) : null;
        $onPage = $myPosition !== null
            && $myPosition >= ($paginated->currentPage() - 1) * LeaderboardService::PER_PAGE + 1
            && $myPosition <= $paginated->currentPage() * LeaderboardService::PER_PAGE;

        return view('public.leaderboard.show', [
            'assessment' => $assessment,
            'rows' => $pageRows,
            'paginated' => $paginated,
            'locked' => false,
            'totalParticipants' => $board->count(),
            'myPosition' => $myPosition,
            'myRow' => $myRow,
            'jumpPage' => $jumpPage,
            'onPage' => $onPage,
            'variant' => 'assessment',
        ]);
    }

    private function paginate(Collection $rows, int $perPage): Paginator
    {
        $page = Paginator::resolveCurrentPage();
        $slice = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return new Paginator($slice, $rows->count(), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
        ])->appends(request()->query());
    }
}
