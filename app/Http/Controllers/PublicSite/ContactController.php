<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicSite\ContactRequest;
use App\Models\ContactMessage;
use App\Notifications\NewContactMessageNotification;
use App\Support\AdminNotifier;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function create()
    {
        return view('public.contact');
    }

    public function store(ContactRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();

        $message = ContactMessage::create([
            'user_id' => $user?->id,
            'name' => $user?->name ?? $validated['name'],
            'email' => $user?->email ?? $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'new',
        ]);

        AdminNotifier::notifySuperAdmins(new NewContactMessageNotification(
            $message->name,
            $message->email,
            $message->subject,
            $message->message,
        ));

        return redirect()
            ->route('contact.create')
            ->with('success', 'Your message has been sent successfully.')
            ->with('reference_code', $message->reference_code);
    }
}
