@extends('layouts.admin')

@section('title', $message->subject . ' - Contact Messages - ' . config('app.name'))

@section('header', 'Contact Message')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('admin.contact-messages.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                &larr; Back to inbox
            </a>
            <h2 class="mt-2 font-display text-xl font-bold text-slate-900 dark:text-slate-100">{{ $message->subject }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                <span class="font-mono">{{ $message->reference_code }}</span>
                &middot; {{ $message->created_at?->format('M j, Y g:i A') ?? '—' }}
            </p>
        </div>
        <x-ui.badge :variant="match($message->status) {
            'new' => 'warning',
            'replied' => 'success',
            'closed' => 'default',
            default => 'default',
        }">
            {{ ucfirst($message->status) }}
        </x-ui.badge>
    </div>

    <x-ui.card>
        <div class="border-b border-slate-200 pb-4 dark:border-slate-800">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                From: <span class="font-medium text-slate-700 dark:text-slate-200">{{ $message->name }}</span>
                ({{ $message->email }})
                @if($message->user)
                    <span class="ml-1"> &middot; registered member</span>
                @endif
            </p>
        </div>
        <div class="pt-4">
            <p class="whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">{{ $message->message }}</p>
        </div>
    </x-ui.card>

    @if($message->replies->isNotEmpty())
        <x-ui.card>
            <h3 class="font-display text-lg font-semibold text-slate-900 dark:text-slate-100">Replies</h3>
            <div class="mt-4 space-y-4">
                @foreach($message->replies as $reply)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/40">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ $reply->admin?->name ?? 'LabGato Staff' }}
                                <span class="ml-1 text-xs font-normal text-slate-500 dark:text-slate-400">({{ $reply->created_at?->format('M j, Y g:i A') ?? '—' }})</span>
                            </p>
                        </div>
                        <p class="mt-2 whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">{{ $reply->reply }}</p>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    @endif

    @if($message->status !== 'closed' && auth()->user()->hasPermission('contact-messages.reply'))
        <x-ui.card>
            <h3 class="font-display text-lg font-semibold text-slate-900 dark:text-slate-100">Reply</h3>
            <form method="POST" action="{{ route('admin.contact-messages.reply', $message) }}" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label for="reply" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Reply message</label>
                    <x-ui.textarea
                        id="reply"
                        name="reply"
                        rows="6"
                        placeholder="Write your reply to {{ $message->name }}..."
                        required
                    >{{ old('reply') }}</x-ui.textarea>
                    @error('reply')
                        <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2">
                    <x-ui.button type="submit" variant="primary">Send Reply</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    @endif
</div>
@endsection
