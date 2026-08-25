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

        @if(auth()->user()->isSuperAdministrator())
            @php
                $signaturePath = (string) setting('certificate.signature_path', '');
                $hasSignature = $signaturePath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($signaturePath);
            @endphp
            <x-ui.card title="Certificate">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <x-form.label for="certificate_signatory_name">Signatory name</x-form.label>
                        <x-ui.input
                            id="certificate_signatory_name"
                            name="settings[certificate.signatory_name]"
                            value="{{ old('settings.certificate.signatory_name', setting('certificate.signatory_name', 'Program Director')) }}"
                        />
                    </div>
                    <div>
                        <x-form.label for="certificate_signatory_title">Signatory title</x-form.label>
                        <x-ui.input
                            id="certificate_signatory_title"
                            name="settings[certificate.signatory_title]"
                            value="{{ old('settings.certificate.signatory_title', setting('certificate.signatory_title', 'Program Director, LabGOTO')) }}"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <x-form.label for="certificate_signature">Signature image</x-form.label>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            A PNG/JPG/SVG signature with a transparent background. It is placed above the signatory name on every issued certificate.
                        </p>
                        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-start">
                            <div class="w-full sm:w-64">
                                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Current signature</p>
                                <div class="flex h-24 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white px-4 dark:border-slate-600 dark:bg-slate-800">
                                    @if($hasSignature)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($signaturePath) }}" alt="Current certificate signature" class="max-h-20 max-w-full object-contain">
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500">No signature uploaded</span>
                                    @endif
                                </div>
                                @if($hasSignature)
                                    <p class="mt-1 text-[11px] text-emerald-600 dark:text-emerald-400">Preview shown above will appear on certificates.</p>
                                @endif
                            </div>
                            <div class="flex-1 space-y-3">
                                <x-form.label for="certificate_signature">Upload new</x-form.label>
                                <x-ui.input type="file" id="certificate_signature" name="certificate_signature" accept="image/png,image/jpeg,image/svg+xml" />
                                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <input type="checkbox" name="certificate_signature_remove" value="1" class="rounded border-slate-300 text-danger-600 focus:ring-danger-500 dark:border-slate-600 dark:bg-slate-800">
                                    Remove current signature
                                </label>
                            </div>
                        </div>
                        <x-form.error for="certificate_signature" />
                    </div>
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button type="submit">
                    Save Settings
                </x-ui.button>
            </div>
        @endif
    </form>
</div>
@endsection
