<?php

namespace App\Http\Controllers;

use App\Models\RecentlyViewed;
use App\Services\RecentlyViewedService;
use Illuminate\Http\Request;

class RecentlyViewedController extends Controller
{
    public function __construct(private readonly RecentlyViewedService $recentlyViewed) {}

    public function index(Request $request)
    {
        $recent = $this->recentlyViewed->getRecent($request->user(), 20);

        return view('recently-viewed.index', compact('recent'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'resource_type' => ['required', 'string', 'max:255'],
            'resource_id' => ['required', 'integer'],
        ]);

        $user = $request->user();

        if ($user) {
            $this->recentlyViewed->track($user, $request->input('resource_type'), $request->input('resource_id'));
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, int $id)
    {
        $deleted = RecentlyViewed::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => (bool) $deleted, 'message' => $deleted ? 'Item removed' : 'Item not found']);
        }

        return back()->with('success', 'Item removed from history.');
    }

    public function clear(Request $request)
    {
        $this->recentlyViewed->clear($request->user()->id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'History cleared']);
        }

        return back()->with('success', 'History cleared.');
    }
}
