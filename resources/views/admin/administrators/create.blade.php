@extends('layouts.admin')

@section('title', 'Add Administrator - ' . config('app.name'))

@section('header', 'Add Administrator')

@section('content')
<div class="mx-auto max-w-2xl">
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
                    <x-form.label for="password" required>Password</x-form.label>
                    <x-ui.input
                        id="password"
                        type="password"
                        name="password"
                        :error="$errors->first('password')"
                        aria-required="true"
                    />
                    <x-form.error for="password" />
                </div>

                <div>
                    <x-form.label for="password_confirmation" required>Confirm Password</x-form.label>
                    <x-ui.input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        :error="$errors->first('password_confirmation')"
                        aria-required="true"
                    />
                    <x-form.error for="password_confirmation" />
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

                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', true))
                            class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600"
                        >
                        Active
                    </label>
                </div>
            </div>
        </x-ui.card>

        <div class="flex items-center justify-end gap-3">
            <x-ui.button href="{{ route('admin.administrators.index') }}" variant="ghost">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit">
                Create Administrator
            </x-ui.button>
        </div>
    </form>
</div>
@endsection
