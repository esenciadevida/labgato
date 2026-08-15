@extends('layouts.public')

@section('title', 'My Messages - ' . config('app.name'))

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100">My Messages</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Messages you've sent to LabGato, with replies from our team.
                </p>
            </div>
            <x-ui.button href="{{ route('contact.create') }}" size="sm">New Message</x-ui.button>
        </div>

        <x-member.navigation class="mt-8" />

        <div class="mt-6">
            <x-ui.card>
                @if($messages->isEmpty())
                    <x-ui.empty-state
                        title="No messages yet"
                        description="Messages you send through the contact form will appear here with any replies from our team."
                    >
                        <x-slot:action>
                            <x-ui.button href="{{ route('contact.create') }}">Contact Us</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @else
                    <div class="space-y-3">
                        @foreach($messages as $message)
                            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-800" x-data="{ open: false }">
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="flex w-full items-center justify-between gap-3 text-left"
                                    :aria-expanded="open"
                                >
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $message->subject }}</span>
                                            <x-ui.badge :variant="match($message->status) {
                                                'new' => 'warning',
                                                'replied' => 'success',
                                                'closed' => 'default',
                                                default => 'default',
                                            }">
                                                {{ ucfirst($message->status) }}
                                            </x-ui.badge>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            <span class="font-mono">{{ $message->reference_code }}</span>
                                            &middot; {{ $message->created_at?->format('M j, Y g:i A') ?? '—' }}
                                        </p>
                                    </div>
                                    <svg class="h-5 w-5 shrink-0 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" x-collapse class="mt-4 space-y-4">
                                    <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-900/40">
                                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">You sent</p>
                                        <p class="mt-2 whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">{{ $message->message }}</p>
                                    </div>

                                    @if($message->replies->isEmpty())
                                        <p class="text-sm text-slate-500 dark:text-slate-400">No replies yet. Our team will get back to you soon.</p>
                                    @else
                                        @foreach($message->replies as $reply)
                                            <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-800">
                                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                                    LabGato &middot; {{ $reply->created_at?->format('M j, Y g:i A') ?? '—' }}
                                                </p>
                                                <p class="mt-2 whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">{{ $reply->reply }}</p>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($messages->hasPages())
                        <div class="mt-6 border-t border-slate-200 pt-4 dark:border-slate-800">
                            {{ $messages->links() }}
                        </div>
                    @endif
                @endif
            </x-ui.card>
        </div>
    </div>
</section>
@endsection
