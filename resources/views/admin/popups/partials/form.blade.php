@props([
    'popup' => null,
    'action' => '',
    'method' => 'POST',
    'submitLabel' => 'Save Pop-up',
])

@php
    $oldValue = fn (string $field) => old($field, $popup?->{$field});
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-form.section
            title="Pop-up Content"
            description="The message shown to visitors on public pages."
        >
            <div>
                <x-form.label for="title" required>Title</x-form.label>
                <x-ui.input
                    id="title"
                    name="title"
                    value="{{ $oldValue('title') }}"
                    placeholder="Welcome to Labgoto"
                    :error="$errors->first('title')"
                    aria-required="true"
                />
            </div>

            <div>
                <x-form.label for="content" required>Content</x-form.label>
                <x-ui.textarea id="content" name="content" rows="6" :error="$errors->first('content')" aria-required="true">{{ $oldValue('content') }}</x-ui.textarea>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Plain text. HTML is sanitized before rendering.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-form.label for="cta_text">CTA button text</x-form.label>
                    <x-ui.input id="cta_text" name="cta_text" value="{{ $oldValue('cta_text') }}" placeholder="Learn more" :error="$errors->first('cta_text')" />
                </div>

                <div>
                    <x-form.label for="cta_url">CTA button URL</x-form.label>
                    <x-ui.input id="cta_url" name="cta_url" value="{{ $oldValue('cta_url') }}" placeholder="https://..." :error="$errors->first('cta_url')" />
                </div>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Trigger" description="When and how the pop-up appears.">
            <div x-data="{ trigger: @js(old('trigger_type', $popup?->trigger_type ?? 'on_load')) }">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-form.label for="trigger_type">Trigger type</x-form.label>
                        <x-ui.select id="trigger_type" name="trigger_type" x-model="trigger">
                            <option value="on_load">On page load</option>
                            <option value="after_delay">After delay</option>
                        </x-ui.select>
                        <x-form.error for="trigger_type" />
                    </div>

                    <div x-show="trigger === 'after_delay'" x-cloak>
                        <x-form.label for="delay_seconds">Delay (seconds)</x-form.label>
                        <x-ui.input
                            type="number"
                            id="delay_seconds"
                            name="delay_seconds"
                            value="{{ $oldValue('delay_seconds') ?? 0 }}"
                            min="0"
                            max="86400"
                            :error="$errors->first('delay_seconds')"
                        />
                    </div>
                </div>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Display" description="Visibility, dismissal, and scheduling.">
            <div class="flex flex-wrap gap-6">
                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $popup?->is_active ?? false)) class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                    Active
                </label>

                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="is_dismissible" value="1" @checked(old('is_dismissible', $popup?->is_dismissible ?? true)) class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                    Dismissible (visitors can close it)
                </label>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-form.label for="starts_at">Starts at</x-form.label>
                    <x-ui.input
                        type="datetime-local"
                        id="starts_at"
                        name="starts_at"
                        value="{{ old('starts_at', $popup?->starts_at?->format('Y-m-d\TH:i')) }}"
                        :error="$errors->first('starts_at')"
                    />
                </div>

                <div>
                    <x-form.label for="ends_at">Ends at</x-form.label>
                    <x-ui.input
                        type="datetime-local"
                        id="ends_at"
                        name="ends_at"
                        value="{{ old('ends_at', $popup?->ends_at?->format('Y-m-d\TH:i')) }}"
                        :error="$errors->first('ends_at')"
                    />
                </div>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Leave both empty to show indefinitely while active.</p>
        </x-form.section>
    </x-ui.card>

    <div class="flex items-center justify-end gap-3">
        <x-ui.button href="{{ route('admin.popups.index') }}" variant="ghost">
            Cancel
        </x-ui.button>
        <x-ui.button type="submit">
            {{ $submitLabel }}
        </x-ui.button>
    </div>
</form>
