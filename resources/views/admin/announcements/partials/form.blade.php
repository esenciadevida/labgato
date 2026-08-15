@props([
    'announcement' => null,
    'action' => '',
    'method' => 'POST',
    'submitLabel' => 'Save Announcement',
])

@php
    $oldValue = fn (string $field) => old($field, $announcement?->{$field});
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-form.section
            title="Announcement"
            description="A short banner shown at the very top of public pages."
        >
            <div>
                <x-form.label for="message" required>Message</x-form.label>
                <x-ui.input
                    id="message"
                    name="message"
                    value="{{ $oldValue('message') }}"
                    placeholder="Scheduled maintenance on Saturday 8 PM"
                    :error="$errors->first('message')"
                    maxlength="500"
                    aria-required="true"
                />
            </div>

            <div>
                <x-form.label for="type">Type</x-form.label>
                <x-ui.select id="type" name="type">
                    @foreach(\App\Models\Announcement::TYPES as $typeOption)
                        <option value="{{ $typeOption }}" @selected(old('type', $announcement?->type ?? 'info') === $typeOption)>
                            {{ ucfirst($typeOption) }}
                        </option>
                    @endforeach
                </x-ui.select>
                <x-form.error for="type" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-form.label for="link_text">Link text</x-form.label>
                    <x-ui.input id="link_text" name="link_text" value="{{ $oldValue('link_text') }}" placeholder="Learn more" :error="$errors->first('link_text')" />
                </div>

                <div>
                    <x-form.label for="link_url">Link URL</x-form.label>
                    <x-ui.input id="link_url" name="link_url" value="{{ $oldValue('link_url') }}" placeholder="https://..." :error="$errors->first('link_url')" />
                </div>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Display" description="Visibility, dismissal, and scheduling.">
            <div class="flex flex-wrap gap-6">
                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $announcement?->is_active ?? false)) class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                    Active
                </label>

                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="is_dismissible" value="1" @checked(old('is_dismissible', $announcement?->is_dismissible ?? true)) class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
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
                        value="{{ old('starts_at', $announcement?->starts_at?->format('Y-m-d\TH:i')) }}"
                        :error="$errors->first('starts_at')"
                    />
                </div>

                <div>
                    <x-form.label for="ends_at">Ends at</x-form.label>
                    <x-ui.input
                        type="datetime-local"
                        id="ends_at"
                        name="ends_at"
                        value="{{ old('ends_at', $announcement?->ends_at?->format('Y-m-d\TH:i')) }}"
                        :error="$errors->first('ends_at')"
                    />
                </div>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Leave both empty to show indefinitely while active.</p>
        </x-form.section>
    </x-ui.card>

    <div class="flex items-center justify-end gap-3">
        <x-ui.button href="{{ route('admin.announcements.index') }}" variant="ghost">
            Cancel
        </x-ui.button>
        <x-ui.button type="submit">
            {{ $submitLabel }}
        </x-ui.button>
    </div>
</form>
