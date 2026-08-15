<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $messages = ContactMessage::query()
            ->where('user_id', $request->user()->id)
            ->with(['replies.admin'])
            ->orderByDesc('created_at')
            ->paginate((int) config('labgoto.pagination.per_page', 20));

        return view('member.messages', compact('messages'));
    }
}
