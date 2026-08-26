@extends('layouts.admin')

@section('title', 'Settings - ' . config('app.name'))

@section('header', 'Settings')

@section('content')
<div class="mx-auto max-w-3xl">

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @foreach($grouped as $categoryKey => $category)
            <x-ui.card :title="$category['label']">
                @if($category['settings']->isEmpty())
                    <p class="text-sm text-slate-500 dark:text-slate-400">No settings configured for this category.</p>
                @else
                    <div class="space-y-5">
                        @foreach($category['settings'] as $setting)
                            <div>
                                <x-form.label for="setting-{{ $setting->key }}">
                                    {{ $setting->label ?: Str::afterLast($setting->key, '.') }}
                                </x-form.label>

                                @if($setting->description)
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $setting->description }}</p>
                                @endif

                                <div class="mt-2">
                                    @switch($setting->type)
                                        @case('text')
                                            <textarea
                                                id="setting-{{ $setting->key }}"
                                                name="settings[{{ $setting->key }}]"
                                                rows="3"
                                                class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-card focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-primary-500"
                                            >{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                            @break

                                        @case('number')
                                            <x-ui.input
                                                type="number"
                                                id="setting-{{ $setting->key }}"
                                                name="settings[{{ $setting->key }}]"
                                                value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                step="any"
                                                :error="$errors->first('settings.' . $setting->key)"
                                            />
                                            @break

                                        @case('boolean')
                                            <input type="hidden" name="settings[{{ $setting->key }}]" value="false">
                                            <label class="inline-flex cursor-pointer select-none items-center gap-2">
                                                <input
                                                    type="checkbox"
                                                    name="settings[{{ $setting->key }}]"
                                                    value="true"
                                                    class="h-4 w-4 rounded border-slate-300 text-primary-500 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800"
                                                    @checked(old('settings.' . $setting->key, $setting->value) === 'true')
                                                >
                                                <span class="text-sm text-slate-700 dark:text-slate-300">Enabled</span>
                                            </label>
                                            @break

                                        @case('select')
                                            <select
                                                id="setting-{{ $setting->key }}"
                                                name="settings[{{ $setting->key }}]"
                                                class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-card focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-primary-500"
                                            >
                                                @foreach($setting->options ?? [] as $optionKey => $optionLabel)
                                                    <option
                                                        value="{{ is_string($optionKey) ? $optionKey : $optionLabel }}"
                                                        @selected(old('settings.' . $setting->key, $setting->value) == (is_string($optionKey) ? $optionKey : $optionLabel))
                                                    >
                                                        {{ $optionLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @break

                                        @default
                                            <x-ui.input
                                                id="setting-{{ $setting->key }}"
                                                name="settings[{{ $setting->key }}]"
                                                value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                :error="$errors->first('settings.' . $setting->key)"
                                            />
                                    @endswitch
                                </div>

                                <x-form.error for="settings.{{ $setting->key }}" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        @endforeach

        <div class="flex items-center justify-end gap-3">
            <x-ui.button type="submit">
                Save Settings
            </x-ui.button>
        </div>
    </form>
</div>
@endsection
