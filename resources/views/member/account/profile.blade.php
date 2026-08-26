@extends('layouts.member')

@section('title', 'My Profile - ' . config('app.name'))

@section('content')
<section style="padding-bottom: 6rem">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

        @php
            $profile = $user->profile;
            $completion = $profile->completionPercent();
            $displayName = $profile->display_name ?: $user->name;
            $roleLabel = $user->isAdmin() ? 'Administrator' : ($profile->statusLabel() ?: 'Member');
            $initials = collect(preg_split('/\s+/', trim($displayName)))
                ->filter()
                ->map(fn (string $word) => strtoupper(mb_substr($word, 0, 1)))
                ->take(2)
                ->implode('') ?: '?';
        @endphp

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100">
                    My Profile
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Manage your account details and personal information.
                </p>
            </div>
            <a href="{{ route('contributors.show', $user->profile_slug) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                View public profile
            </a>
        </div>

        <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data"
              class="mt-8"
              x-data="{
                  status: '{{ old('professional_status', $profile->professional_status ?? '') }}',
                  preview: null,
                  removed: false,
                  choosePhoto() { document.getElementById('photo').click(); },
                  setPhoto(event) {
                      const file = event.target.files[0];
                      this.preview = file ? URL.createObjectURL(file) : null;
                      this.removed = false;
                  },
                  clearPhoto() { this.preview = null; this.removed = true; }
              }">

            @csrf
            @method('PUT')

            {{-- Profile hero with cover banner and editable photo --}}
            <div class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-800">
                <div class="h-24 bg-gradient-to-r from-primary-50 via-slate-50 to-primary-50 border-b border-slate-100 dark:from-slate-800 dark:via-slate-900 dark:to-slate-800 dark:border-slate-700" aria-hidden="true"></div>

                <div class="px-6 pb-6 -mt-12 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-end">
                        <div class="relative group shrink-0">
                            <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp" class="hidden" @change="setPhoto($event)">
                            <input type="checkbox" id="remove_photo" name="remove_photo" value="1" class="hidden" x-model="removed">

                            <div class="h-24 w-24 overflow-hidden rounded-full bg-slate-100 ring-4 ring-white shadow-md dark:bg-slate-700 dark:ring-slate-800">
                                <template x-if="preview">
                                    <img :src="preview" alt="" class="h-full w-full object-cover" loading="lazy">
                                </template>
                                <template x-if="!preview && removed">
                                    <div class="flex h-full w-full items-center justify-center bg-primary-600 font-semibold text-white">{{ $initials }}</div>
                                </template>
                                <template x-if="!preview && !removed">
                                    @if($profile->photo_url)
                                        <img src="{{ $profile->photo_url }}" alt="{{ $displayName }}" class="h-full w-full object-cover" loading="lazy">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-primary-600 font-semibold text-white">{{ $initials }}</div>
                                    @endif
                                </template>
                            </div>

                            <label for="photo" title="Change photo"
                                   class="absolute inset-0 flex cursor-pointer items-center justify-center rounded-full bg-black/40 text-xs font-medium text-white opacity-0 transition-opacity group-hover:opacity-100">
                                Change
                            </label>
                            <x-form.error for="photo" />
                        </div>

                        <div class="space-y-1 pb-1">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h2 class="font-display text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ $displayName }}</h2>
                                <span class="inline-flex items-center rounded-full border border-primary-100 bg-primary-50 px-2.5 py-0.5 text-xs font-semibold text-primary-700 dark:border-primary-800 dark:bg-primary-900/30 dark:text-primary-300">
                                    {{ $roleLabel }}
                                </span>
                            </div>
                            @if($user->bio)
                                <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2">{{ $user->bio }}</p>
                            @else
                                <p class="text-sm italic text-slate-500 dark:text-slate-400">No bio added yet.</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 self-start sm:flex-row sm:items-center md:self-end">
                        <span class="hidden text-xs text-slate-400 lg:inline-block dark:text-slate-500">JPG, PNG, or WebP · up to 2MB.</span>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="choosePhoto()"
                                    class="rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
                                Upload new photo
                            </button>
                            @if($profile->photo_path)
                                <button type="button" @click="clearPhoto()" x-show="!removed"
                                        class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-danger-600 transition-colors hover:border-danger-200 hover:bg-danger-50 dark:border-slate-600 dark:bg-slate-800 dark:text-danger-400 dark:hover:bg-slate-700">
                                    Remove photo
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <aside class="lg:col-span-1 space-y-6">
                    <x-ui.card>
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
                    </x-ui.card>

                    <x-ui.card title="Privacy">
                        <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Choose what is visible on your public contributor profile.</p>
                        <div class="space-y-3">
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
                    </x-ui.card>

                    <x-ui.card title="Account Details">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Email</dt>
                                <dd class="mt-1 break-all text-sm text-slate-900 dark:text-slate-100">{{ $user->email }}</dd>
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
                                <dt class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Last login</dt>
                                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $user->last_login_at?->format('M j, Y') ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</dt>
                                <dd>
                                    <x-ui.badge variant="success">
                                        <span class="mr-1.5 inline-flex h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                        Active
                                    </x-ui.badge>
                                </dd>
                            </div>
                        </dl>
                    </x-ui.card>
                </aside>

                <main class="lg:col-span-2 space-y-6">
                    <x-ui.card title="Personal Information">
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
                        </div>
                    </x-ui.card>

                    <x-ui.card title="Professional Status">
                        <div>
                            <x-form.label for="professional_status" required>Professional status</x-form.label>
                            <select
                                id="professional_status"
                                name="professional_status"
                                x-model="status"
                                class="input-base mt-1.5 w-full @error('professional_status') input-error @enderror"
                                aria-required="true"
                            >
                                <option value="">Select your status</option>
                                @foreach(\App\Models\UserProfile::STATUS_LABELS as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}" @selected(old('professional_status', $profile->professional_status) === $statusValue)>{{ $statusLabel }}</option>
                                @endforeach
                            </select>
                            <x-form.error for="professional_status" />
                        </div>

                        <div x-show="['student', 'intern'].includes(status)" x-cloak class="mt-5 space-y-5 border-t border-slate-100 pt-5 dark:border-slate-700">
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

                        <div x-show="['scientist', 'technician', 'professional', 'researcher', 'lecturer'].includes(status)" x-cloak class="mt-5 space-y-5 border-t border-slate-100 pt-5 dark:border-slate-700">
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
                    </x-ui.card>

                    <x-ui.card title="Bio">
                        <x-form.label for="bio">About you</x-form.label>
                        <x-ui.textarea id="bio" name="bio" rows="4">{{ old('bio', $user->bio) }}</x-ui.textarea>
                        <x-form.error for="bio" />
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                            A short description shown on your public contributor profile.
                        </p>
                    </x-ui.card>

                    <div class="sticky bottom-4 z-10 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 shadow-card sm:px-6 py-4 dark:border-slate-700 dark:bg-slate-800">
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Changes apply to your public contributor profile.
                        </p>
                        <x-ui.button type="submit" variant="primary">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Changes
                        </x-ui.button>
                    </div>
                </main>
            </div>
        </form>

        @if($user->hasCustomPassword())
        <div class="mt-6">
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
        </div>
        @else
        <div class="mt-6">
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
        </div>
        @endif

    </div>
</section>
@endsection
