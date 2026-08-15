@extends('layouts.member')

@section('title', 'My Profile - ' . config('app.name'))

@section('content')
<section style="padding-bottom: 6rem">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100">
                    My Profile
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Manage your account details and personal information.
                </p>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-800">
                    <div class="flex flex-col items-center text-center">
                        <x-public.avatar :name="$user->name" :user-id="$user->id" size="lg" />
                        <h2 class="mt-4 font-display text-xl font-bold text-slate-900 dark:text-slate-100">
                            {{ $user->name }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $user->isAdmin() ? 'Administrator' : 'Member' }}
                        </p>
                        @if($user->bio)
                            <p class="mt-4 text-sm text-slate-600 dark:text-slate-300 line-clamp-4">
                                {{ $user->bio }}
                            </p>
                        @else
                            <p class="mt-4 text-sm italic text-slate-400 dark:text-slate-500">
                                No bio yet — add one below to tell the community about yourself.
                            </p>
                        @endif
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-5 dark:border-slate-700">
                        <dl class="space-y-3">
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Member since</dt>
                                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $user->created_at?->format('F Y') ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Last login</dt>
                                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $user->last_login_at?->format('M j, Y') ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-2">
                <x-ui.card title="Personal Information">
                    <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-form.label for="name" required>Full name</x-form.label>
                            <x-ui.input
                                id="name"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                :error="$errors->first('name')"
                                autocomplete="name"
                                aria-required="true"
                            />
                            <x-form.error for="name" />
                        </div>

                        <div>
                            <x-form.label for="email">Email address</x-form.label>
                            <x-ui.input id="email" type="email" value="{{ $user->email }}" disabled />
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                Your email is your sign-in and is kept private.
                            </p>
                        </div>

                        <div>
                            <x-form.label for="bio">Bio</x-form.label>
                            <x-ui.textarea id="bio" name="bio" rows="4">{{ old('bio', $user->bio) }}</x-ui.textarea>
                            <x-form.error for="bio" />
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                A short description shown on your public contributor profile.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5 dark:border-slate-700">
                            <x-ui.button type="submit" variant="primary">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save Changes
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card>

                <x-ui.card title="Account Details">
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Email</dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100 break-all">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Account type</dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $user->isAdmin() ? 'Administrator' : 'Member' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Member since</dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $user->created_at?->format('M j, Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</dt>
                            <dd class="mt-1">
                                <x-ui.badge variant="success">
                                    <span class="inline-flex h-1.5 w-1.5 rounded-full bg-current mr-1.5" aria-hidden="true"></span>
                                    Active
                                </x-ui.badge>
                            </dd>
                        </div>
                    </dl>
                </x-ui.card>
            </div>
        </div>
    </div>
</section>
@endsection
