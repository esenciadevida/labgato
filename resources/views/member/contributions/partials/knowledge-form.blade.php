@props([
    'contribution' => null,
    'action' => '',
    'method' => 'POST',
])

@php
    $oldValue = fn (string $field) => old($field, $contribution?->{$field});
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-form.section
            title="Basic Information"
            description="Provide a clear title, a short summary, and the body of your knowledge article."
        >
            <div>
                <x-form.label for="name" required>Name</x-form.label>
                <x-ui.input
                    id="name"
                    name="name"
                    value="{{ $oldValue('name') }}"
                    placeholder="Understanding Complete Blood Count Results"
                    :error="$errors->first('name')"
                    aria-required="true"
                />
            </div>

            <div>
                <x-form.label for="short_description" required>Short description</x-form.label>
                <x-ui.textarea id="short_description" name="short_description" rows="3">{{ $oldValue('short_description') }}</x-ui.textarea>
                <x-form.error for="short_description" />
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Content"
            description="Write the body in Markdown. HTML is sanitized before rendering."
        >
            <div>
                <x-form.label for="content" required>Content (Markdown)</x-form.label>
                <x-form.markdown-editor id="content" name="content" type="knowledge" rows="16">{{ $oldValue('content') }}</x-form.markdown-editor>
                <x-form.error for="content" />
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Metadata"
            description="Optional details to help organize your article."
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-form.label for="topic_category">Topic category</x-form.label>
                    <x-ui.input id="topic_category" name="topic_category" value="{{ $oldValue('topic_category') }}" placeholder="Hematology" :error="$errors->first('topic_category')" />
                </div>

                <div>
                    <x-form.label for="difficulty_level">Difficulty level</x-form.label>
                    <x-ui.select id="difficulty_level" name="difficulty_level">
                        <option value="">Select difficulty</option>
                        @foreach(['Beginner', 'Intermediate', 'Advanced'] as $level)
                            <option value="{{ $level }}" @selected(old('difficulty_level', $contribution?->difficulty_level) === $level)>
                                {{ $level }}
                            </option>
                        @endforeach
                    </x-ui.select>
                    <x-form.error for="difficulty_level" />
                </div>
            </div>

            <div>
                <x-form.label for="learning_objectives">Learning objectives</x-form.label>
                <x-ui.textarea id="learning_objectives" name="learning_objectives" rows="4">{{ $oldValue('learning_objectives') }}</x-ui.textarea>
                <x-form.error for="learning_objectives" />
            </div>
        </x-form.section>
    </x-ui.card>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <x-ui.button href="{{ route('account.contributions.index') }}" variant="ghost">
            Cancel
        </x-ui.button>
        <x-ui.button type="submit" variant="secondary" name="action" value="draft">
            Save Draft
        </x-ui.button>
        <x-ui.button type="submit" name="action" value="submit">
            Submit for Review
        </x-ui.button>
    </div>
</form>
