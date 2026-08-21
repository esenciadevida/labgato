@extends('layouts.admin')

@section('title', 'Invite Administrator - ' . config('app.name'))

@section('header', 'Invite Administrator')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-4 rounded-lg border border-info-200 bg-info-50 px-4 py-3 text-sm text-info-800 dark:border-info-500/30 dark:bg-info-500/10 dark:text-info-300">
        The invited administrator will receive an email link to set up their own password. You assign the role now — they choose their credentials.
    </div>

    <form method="POST" action="{{ route('admin.administrators.store') }}" class="space-y-6">
        @csrf

        <x-ui.card>
            <div class="space-y-4">
                <div>
                    <x-form.label for="name" required>Name</x-form.label>
                    <x-ui.input
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        :error="$errors->first('name')"
                        aria-required="true"
                    />
                    <x-form.error for="name" />
                </div>

                <div>
                    <x-form.label for="email" required>Email</x-form.label>
                    <x-ui.input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        :error="$errors->first('email')"
                        aria-required="true"
                    />
                    <x-form.error for="email" />
                </div>

                <div>
                    <x-form.label for="role_id" required>Role</x-form.label>
                    <x-ui.select id="role_id" name="role_id" :error="$errors->first('role_id')">
                        <option value="">Select a role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </x-ui.select>
                    <x-form.error for="role_id" />
                </div>
            </div>
        </x-ui.card>

        <div class="flex items-center justify-end gap-3">
            <x-ui.button href="{{ route('admin.administrators.index') }}" variant="ghost">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit">
                Send Invitation
            </x-ui.button>
        </div>
    </form>
</div>
@endsection
