@extends('layouts.admin')

@section('title', 'Launch Control - ' . config('app.name'))

@section('header', 'Launch Control')

@section('content')
@php
    $statusLabels = ['locked' => 'Locked', 'available' => 'Available', 'open' => 'Open'];
@endphp
<div class="mx-auto max-w-4xl">

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <h2 class="font-display text-xl font-bold uppercase tracking-widest text-slate-900 dark:text-slate-100">
            Labgoto Launch Control
        </h2>
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wider
            {{ $settings->isLive()
                ? 'bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-300'
                : 'bg-warning-100 text-warning-700 dark:bg-warning-900/40 dark:text-warning-300' }}">
            {{ $settings->isLive() ? 'Live' : 'Pre-Launch' }}
        </span>
    </div>

    <form method="POST" action="{{ route('admin.launch') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-ui.card title="Launch Moment">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <x-form.label for="launch_date" required>Launch date</x-form.label>
                    <x-ui.input id="launch_date" name="launch_date" type="date"
                        value="{{ old('launch_date', $settings->launchAtCarbon()?->format('Y-m-d')) }}"
                        :error="$errors->first('launch_date')" required />
                    <x-form.error for="launch_date" />
                </div>
                <div>
                    <x-form.label for="launch_time" required>Launch time ({{ $settings->timezone }})</x-form.label>
                    <x-ui.input id="launch_time" name="launch_time" type="time"
                        value="{{ old('launch_time', $settings->launchAtCarbon()?->format('H:i')) }}"
                        :error="$errors->first('launch_time')" required />
                    <x-form.error for="launch_time" />
                </div>
                <div>
                    <x-form.label for="timezone" required>Timezone</x-form.label>
                    <select id="timezone" name="timezone"
                        class="input-base mt-1.5 @error('timezone') input-error @enderror">
                        @foreach($timezones as $tz)
                            <option value="{{ $tz }}" @selected(old('timezone', $settings->timezone) === $tz)>{{ $tz }}</option>
                        @endforeach
                    </select>
                    <x-form.error for="timezone" />
                </div>
            </div>

            <label class="mt-5 flex cursor-pointer items-center gap-3">
                <input type="hidden" name="auto_activate" value="0">
                <input type="checkbox" name="auto_activate" value="1" @checked(old('auto_activate', $settings->auto_activate))
                    class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800">
                <span class="text-sm text-slate-700 dark:text-slate-200">
                    Auto-activate: flip the site live automatically when the launch moment arrives.
                </span>
            </label>

            @if($settings->launchAtCarbon())
                <div class="mt-5 flex flex-wrap items-center gap-6 rounded-xl bg-gradient-to-b from-primary-950 to-slate-950 px-6 py-8">
                    <div class="text-xs font-semibold uppercase tracking-widest text-white/50">
                        Countdown preview<br>
                        <span class="font-normal normal-case tracking-normal text-white/70">{{ $settings->launchAtCarbon()->format('D, M j, Y \a\t H:i') }} ({{ $settings->timezone }})</span>
                        <div class="mt-4">
                            <x-launch.countdown :target="$settings->launchAtCarbon()" size="md" />
                        </div>
                    </div>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card title="Feature Toggles">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <input type="hidden" name="registration_open" value="0">
                    <input type="checkbox" name="registration_open" value="1" @checked(old('registration_open', $settings->registration_open))
                        class="mt-0.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800">
                    <span>
                        <span class="block text-sm font-medium text-slate-900 dark:text-slate-100">Registration</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Allow new account signups on the launch wall.</span>
                    </span>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <input type="hidden" name="challenge_open" value="0">
                    <input type="checkbox" name="challenge_open" value="1" @checked(old('challenge_open', $settings->challenge_open))
                        class="mt-0.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800">
                    <span>
                        <span class="block text-sm font-medium text-slate-900 dark:text-slate-100">Challenge</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Show registered status on the launch dashboard.</span>
                    </span>
                </label>

                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <x-form.label for="results_status">Results</x-form.label>
                    <select id="results_status" name="results_status" class="input-base mt-1.5">
                        @foreach(['locked', 'available'] as $option)
                            <option value="{{ $option }}" @selected(old('results_status', $settings->results_status) === $option)>{{ $statusLabels[$option] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <x-form.label for="answer_review_status">Answer review</x-form.label>
                    <select id="answer_review_status" name="answer_review_status" class="input-base mt-1.5">
                        @foreach(['locked', 'available'] as $option)
                            <option value="{{ $option }}" @selected(old('answer_review_status', $settings->answer_review_status) === $option)>{{ $statusLabels[$option] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <x-form.label for="leaderboard_status">Leaderboard</x-form.label>
                    <select id="leaderboard_status" name="leaderboard_status" class="input-base mt-1.5">
                        @foreach(['locked', 'available'] as $option)
                            <option value="{{ $option }}" @selected(old('leaderboard_status', $settings->leaderboard_status) === $option)>{{ $statusLabels[$option] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rounded-xl border border-danger-200 p-4 dark:border-danger-900">
                    <x-form.label for="main_site_status">Main website</x-form.label>
                    <select id="main_site_status" name="main_site_status" class="input-base mt-1.5">
                        @foreach(['locked', 'open'] as $option)
                            <option value="{{ $option }}" @selected(old('main_site_status', $settings->main_site_status) === $option)>{{ $statusLabels[$option] }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-danger-600 dark:text-danger-400">
                        Danger zone: unlocking the main website makes the full platform public immediately.
                    </p>
                </div>
            </div>
        </x-ui.card>

        <div class="flex justify-end">
            <x-ui.button type="submit" variant="primary">Save Launch Settings</x-ui.button>
        </div>
    </form>
</div>
@endsection
