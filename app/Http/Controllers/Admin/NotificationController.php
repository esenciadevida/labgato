<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()
            ->latest()
            ->paginate(20);

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function clear(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        $request->user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }
}
