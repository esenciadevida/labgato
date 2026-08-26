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
            ->paginate(config('labgoto.pagination.per_page'));

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

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->route('admin.notifications.index')->with('success', 'All notifications marked as read.');
    }

    public function markAsRead(Request $request, $notificationId)
    {
        $notification = $request->user()->notifications()->findOrFail($notificationId);
        $notification->update(['read_at' => now()]);

        return redirect()->route('admin.notifications.index')->with('success', 'Notification marked as read.');
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
