<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Notifications\ContactMessageRepliedNotification;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $messages = ContactMessage::query()
            ->where('user_id', $userId)
            ->with(['replies.admin'])
            ->orderByDesc('created_at')
            ->paginate((int) config('labgoto.pagination.per_page', 20));

        // Opening the inbox marks the member's messages and reply notifications as read.
        ContactMessage::where('user_id', $userId)
            ->whereNull('member_read_at')
            ->update(['member_read_at' => now()]);

        $request->user()->notifications()
            ->where(fn ($query) => $query
                ->where('type', ContactMessageRepliedNotification::class)
                ->orWhere('data->type', 'contact_reply'))
            ->update(['read_at' => now()]);

        return view('member.messages', compact('messages'));
    }
}
