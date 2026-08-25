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

    public function unreadCount(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'contact_unread' => \App\Models\ContactMessage::query()->whereNull('read_at')->count(),
        ]);
    }

    public function clear(Request $request)
    {
        $request->user()->notifications()
            ->where('data->type', 'contact_message')
            ->update(['read_at' => now()]);

        $request->user()->notifications()
            ->where('data->type', 'contact_message')
            ->delete();

        return response()->json([
            'success' => true,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
