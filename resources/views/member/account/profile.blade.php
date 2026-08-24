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
                        <x-public.avatar :name="$user->profile->display_name ?: $user->name" :src="$user->profile->photo_url" size="lg" />
                        <h2 class="mt-4 font-display text-xl font-bold text-slate-900 dark:text-slate-100">
                            {{ $user->name }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $user->isAdmin() ? 'Administrator' : ($user->profile->statusLabel() ?: 'Member') }}
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
                    @php
                        $profile = $user->profile;
                        $completion = $profile->completionPercent();
                    @endphp

                    <div class="mb-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/50">
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <span class="font-medium text-slate-700 dark:text-slate-200">Profile completion</span>
                            <span class="font-semibold {{ $completion >= 100 ? 'text-success-600 dark:text-success-400' : 'text-slate-500 dark:text-slate-400' }}">{{ $completion }}%</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700" role="progressbar" aria-valuenow="{{ $completion }}" aria-valuemin="0" aria-valuemax="100" aria-label="Profile completion">
                            <div class="h-full rounded-full transition-all {{ $completion >= 100 ? 'bg-success-500' : 'bg-primary-600' }}" style="width: {{ $completion }}%"></div>
                        </div>
                        @if($completion < 100)
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                A complete profile builds trust and helps you connect with the community.
                            </p>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data"
                          class="space-y-5"
                          x-data="{ status: '{{ old('professional_status', $profile->professional_status ?? '') }}', photoPreview: null }">

                        @csrf
                        @method('PUT')

                        <div class="flex flex-col items-center gap-5 border-b border-slate-100 pb-6 sm:flex-row dark:border-slate-700">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" alt="" class="inline-block shrink-0 h-16 w-16 rounded-full object-cover">
                            </template>
                            <template x-if="! photoPreview">
                                <x-public.avatar :name="$profile->display_name ?: $user->name" :src="$profile->photo_url" size="lg" />
                            </template>

                            <div class="flex-1 text-center sm:text-left">
                                <x-form.label for="photo">Profile photo</x-form.label>
                                <input
                                    id="photo"
                                    name="photo"
                                    type="file"
                                    accept="image/png,image/jpeg,image/webp"
                                    class="mt-1.5 block w-full cursor-pointer text-sm text-slate-500 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-primary-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-primary-700 dark:text-slate-400 dark:file:bg-primary-600 dark:hover:file:bg-primary-700"
                                    @change="if ($event.target.files[0]) { photoPreview = URL.createObjectURL($event.target.files[0]); document.getElementById('remove_photo').checked = false; }"
                                >
                                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">JPG, PNG, or WebP, up to 2MB.</p>
                                <x-form.error for="photo" />
                                @if($profile->photo_path)
                                    <label class="mt-2 inline-flex items-center gap-2 text-sm text-danger-600 hover:text-danger-700 dark:text-danger-400 cursor-pointer">
                                        <input type="checkbox" id="remove_photo" name="remove_photo" value="1" class="rounded border-slate-300 text-danger-600 focus:ring-danger-500 dark:border-slate-600 dark:bg-slate-800">
                                        Remove current photo
                                    </label>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <x-form.label for="display_name">Display name</x-form.label>
                                <x-ui.input
                                    id="display_name"
                                    name="display_name"
                                    value="{{ old('display_name', $profile->display_name) }}"
                                    :error="$errors->first('display_name')"
                                    maxlength="60"
                                />
                                <x-form.error for="display_name" />
                                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Shown on your public profile instead of your full name.</p>
                            </div>

                            <div>
                                <x-form.label for="country">Country</x-form.label>
                                <x-ui.input
                                    id="country"
                                    name="country"
                                    value="{{ old('country', $profile->country) }}"
                                    :error="$errors->first('country')"
                                    autocomplete="country-name"
                                    maxlength="100"
                                />
                                <x-form.error for="country" />
                            </div>
                        </div>

                        <div>
                            <x-form.label for="professional_status" required>Professional status</x-form.label>
                            <select
                                id="professional_status"
                                name="professional_status"
                                x-model="status"
                                class="input-base mt-1.5 @error('professional_status') input-error @enderror"
                                aria-required="true"
                            >
                                <option value="">Select your status</option>
                                @foreach(\App\Models\UserProfile::STATUS_LABELS as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}" @selected(old('professional_status', $profile->professional_status) === $statusValue)>{{ $statusLabel }}</option>
                                @endforeach
                            </select>
                            <x-form.error for="professional_status" />
                        </div>

                        <div x-show="['student', 'intern'].includes(status)" x-cloak class="space-y-5 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Student details</p>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <x-form.label for="institution">Institution</x-form.label>
                                    <x-ui.input id="institution" name="institution" value="{{ old('institution', $profile->institution) }}" :error="$errors->first('institution')" placeholder="e.g. University of Lagos" />
                                    <x-form.error for="institution" />
                                </div>
                                <div>
                                    <x-form.label for="programme">Programme</x-form.label>
                                    <x-ui.input id="programme" name="programme" value="{{ old('programme', $profile->programme) }}" :error="$errors->first('programme')" placeholder="e.g. Medical Laboratory Science" />
                                    <x-form.error for="programme" />
                                </div>
                                <div>
                                    <x-form.label for="level">Level / Year</x-form.label>
                                    <x-ui.input id="level" name="level" value="{{ old('level', $profile->level) }}" :error="$errors->first('level')" placeholder="e.g. 300 Level" maxlength="150" />
                                    <x-form.error for="level" />
                                </div>
                                <div>
                                    <x-form.label for="graduation_year">Expected graduation year</x-form.label>
                                    <x-ui.input id="graduation_year" name="graduation_year" type="number" min="1980" max="{{ now()->year + 10 }}" value="{{ old('graduation_year', $profile->graduation_year) }}" :error="$errors->first('graduation_year')" />
                                    <x-form.error for="graduation_year" />
                                </div>
                            </div>
                        </div>

                        <div x-show="['scientist', 'technician', 'professional', 'researcher', 'lecturer'].includes(status)" x-cloak class="space-y-5 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Professional details</p>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <x-form.label for="employer">Employer</x-form.label>
                                    <x-ui.input id="employer" name="employer" value="{{ old('employer', $profile->employer) }}" :error="$errors->first('employer')" placeholder="e.g. St. Mary's Teaching Hospital" />
                                    <x-form.error for="employer" />
                                </div>
                                <div>
                                    <x-form.label for="job_title">Job title</x-form.label>
                                    <x-ui.input id="job_title" name="job_title" value="{{ old('job_title', $profile->job_title) }}" :error="$errors->first('job_title')" placeholder="e.g. Senior Medical Laboratory Scientist" />
                                    <x-form.error for="job_title" />
                                </div>
                                <div>
                                    <x-form.label for="years_of_experience">Years of experience</x-form.label>
                                    <x-ui.input id="years_of_experience" name="years_of_experience" type="number" min="0" max="60" value="{{ old('years_of_experience', $profile->years_of_experience) }}" :error="$errors->first('years_of_experience')" />
                                    <x-form.error for="years_of_experience" />
                                </div>
                                <div>
                                    <x-form.label for="specialization">Specialization</x-form.label>
                                    <x-ui.input id="specialization" name="specialization" value="{{ old('specialization', $profile->specialization) }}" :error="$errors->first('specialization')" placeholder="e.g. Clinical Chemistry" />
                                    <x-form.error for="specialization" />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
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

                        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Privacy</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Choose what is visible on your public contributor profile.</p>

                            <div class="mt-4 space-y-3">
                                <label class="flex cursor-pointer items-start gap-3">
                                    <input type="checkbox" name="show_country" value="1" @checked(old('show_country', $profile->show_country)) class="mt-0.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800">
                                    <span class="text-sm text-slate-700 dark:text-slate-200">Show my country</span>
                                </label>
                                <label class="flex cursor-pointer items-start gap-3">
                                    <input type="checkbox" name="show_institution" value="1" @checked(old('show_institution', $profile->show_institution)) class="mt-0.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800">
                                    <span class="text-sm text-slate-700 dark:text-slate-200">Show my institution / employer</span>
                                </label>
                                <label class="flex cursor-pointer items-start gap-3">
                                    <input type="checkbox" name="show_on_leaderboard" value="1" @checked(old('show_on_leaderboard', $profile->show_on_leaderboard)) class="mt-0.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800">
                                    <span class="text-sm text-slate-700 dark:text-slate-200">Include me in leaderboards and rankings</span>
                                </label>
                            </div>
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

                @if($user->hasCustomPassword())
                <x-ui.card title="Change Password">
                    <form method="POST" action="{{ route('account.password.update') }}" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-form.label for="current_password" required>Current password</x-form.label>
                            <x-ui.input
                                id="current_password"
                                name="current_password"
                                type="password"
                                :error="$errors->first('current_password')"
                                autocomplete="current-password"
                                aria-required="true"
                            />
                            <x-form.error for="current_password" />
                        </div>

                        <div>
                            <x-form.label for="password" required>New password</x-form.label>
                            <x-ui.input
                                id="password"
                                name="password"
                                type="password"
                                :error="$errors->first('password')"
                                autocomplete="new-password"
                                aria-required="true"
                            />
                            <x-form.error for="password" />
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                At least {{ config('labgoto.security.password_min_length', 8) }} characters, with letters and numbers.
                            </p>
                        </div>

                        <div>
                            <x-form.label for="password_confirmation" required>Confirm new password</x-form.label>
                            <x-ui.input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                aria-required="true"
                            />
                            <x-form.error for="password_confirmation" />
                        </div>

                        @if($user->hasGoogleAccount())
                            <p class="text-xs text-slate-400 dark:text-slate-500">
                                Your account is linked to Google. Changing your password here won't affect your Google sign-in — you can keep using either method.
                            </p>
                        @endif

                        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5 dark:border-slate-700">
                            <x-ui.button type="submit" variant="primary">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Update Password
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
                @else
                <x-ui.card title="Create a Password">
                    <p class="mb-5 text-sm text-slate-600 dark:text-slate-400">
                        You signed up using Google and don't have a LabGato password yet. Create one to also
                        sign in with your email address — your Google sign-in will keep working either way.
                    </p>

                    <form method="POST" action="{{ route('account.password.update') }}" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-form.label for="password" required>New password</x-form.label>
                            <x-ui.input
                                id="password"
                                name="password"
                                type="password"
                                :error="$errors->first('password')"
                                autocomplete="new-password"
                                aria-required="true"
                            />
                            <x-form.error for="password" />
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                At least {{ config('labgoto.security.password_min_length', 8) }} characters, with letters and numbers.
                            </p>
                        </div>

                        <div>
                            <x-form.label for="password_confirmation" required>Confirm new password</x-form.label>
                            <x-ui.input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                aria-required="true"
                            />
                            <x-form.error for="password_confirmation" />
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5 dark:border-slate-700">
                            <x-ui.button type="submit" variant="primary">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Create Password
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
                @endif

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
