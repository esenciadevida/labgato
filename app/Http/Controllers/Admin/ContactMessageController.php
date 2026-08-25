<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplyContactRequest;
use App\Models\ContactMessage;
use App\Models\ContactReply;
use App\Notifications\ContactMessageRepliedNotification;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->query('status'));

        $messages = ContactMessage::query()
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByRaw("CASE WHEN read_at IS NULL THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate((int) config('labgoto.pagination.per_page', 20));

        // Opening the inbox clears the notification heads-up for new messages.
        $request->user()->notifications()
            ->where('data->type', 'contact_message')
            ->update(['read_at' => now()]);

        return view('admin.contact-messages.index', compact('messages', 'status'));
    }

    public function show(Request $request, ContactMessage $message)
    {
        $message->load('replies.admin', 'user');
        $message->markRead();

        $request->user()->notifications()
            ->where('data->type', 'contact_message')
            ->where(fn ($query) => $query
                ->where('data->contact_message_id', $message->id)
                ->orWhere('data->action_url', 'like', '%'.route('admin.contact-messages.show', $message).'%'))
            ->update(['read_at' => now()]);

        return view('admin.contact-messages.show', compact('message'));
    }

    public function reply(ContactMessage $message, ReplyContactRequest $request)
    {
        $validated = $request->validated();

        ContactReply::create([
            'contact_message_id' => $message->id,
            'admin_id' => $request->user()->id,
            'reply' => $validated['reply'],
        ]);

        $message->update([
            'status' => 'replied',
            'read_at' => now(),
            'member_read_at' => null,
        ]);

        $notification = new ContactMessageRepliedNotification(
            $message,
            $validated['reply'],
            $request->user()->name,
        );

        if ($message->user_id !== null && $message->user) {
            $message->user->notify($notification);
        } else {
            Notification::route('mail', $message->email)->notify($notification);
        }

        app(AuditLogService::class)->logWithSubject(
            'contact-messages.replied',
            $message,
            ['subject' => $message->subject],
            $request,
        );

        return redirect()
            ->route('admin.contact-messages.show', $message)
            ->with('success', 'Reply sent to '.$message->email.'.');
    }
}
