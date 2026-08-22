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
            description="Provide a clear title and a short summary of your interpretation guide."
        >
            <div>
                <x-form.label for="name" required>Name</x-form.label>
                <x-ui.input
                    id="name"
                    name="name"
                    value="{{ $oldValue('name') }}"
                    placeholder="Interpreting Thyroid Function Test Results"
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
                <x-form.markdown-editor id="content" name="content" type="interpretation" rows="16">{{ $oldValue('content') }}</x-form.markdown-editor>
                <x-form.error for="content" />
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Clinical Context"
            description="Optional background on when and how this interpretation is used."
        >
            <div>
                <x-form.label for="clinical_context">Clinical context</x-form.label>
                <x-ui.textarea id="clinical_context" name="clinical_context" rows="6">{{ $oldValue('clinical_context') }}</x-ui.textarea>
                <x-form.error for="clinical_context" />
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
