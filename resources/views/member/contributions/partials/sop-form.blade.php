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
            description="Provide a clear title and a short summary of your procedure."
        >
            <div>
                <x-form.label for="name" required>Name</x-form.label>
                <x-ui.input
                    id="name"
                    name="name"
                    value="{{ $oldValue('name') }}"
                    placeholder="Daily Hematology Analyzer Quality Control"
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
            title="Procedure"
            description="Describe the purpose, scope, and step-by-step procedure. Markdown is supported."
        >
            <div>
                <x-form.label for="purpose" required>Purpose</x-form.label>
                <x-ui.textarea id="purpose" name="purpose" rows="4">{{ $oldValue('purpose') }}</x-ui.textarea>
                <x-form.error for="purpose" />
            </div>

            <div>
                <x-form.label for="scope" required>Scope</x-form.label>
                <x-ui.textarea id="scope" name="scope" rows="4">{{ $oldValue('scope') }}</x-ui.textarea>
                <x-form.error for="scope" />
            </div>

            <div>
                <x-form.label for="procedure_content" required>Procedure steps</x-form.label>
                <x-ui.textarea id="procedure_content" name="procedure_content" rows="16">{{ $oldValue('procedure_content') }}</x-ui.textarea>
                <x-form.error for="procedure_content" />
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Additional Information"
            description="Optional sections for quality control, safety, and references."
        >
            <div>
                <x-form.label for="quality_control">Quality control</x-form.label>
                <x-ui.textarea id="quality_control" name="quality_control" rows="4">{{ $oldValue('quality_control') }}</x-ui.textarea>
                <x-form.error for="quality_control" />
            </div>

            <div>
                <x-form.label for="safety_information">Safety information</x-form.label>
                <x-ui.textarea id="safety_information" name="safety_information" rows="4">{{ $oldValue('safety_information') }}</x-ui.textarea>
                <x-form.error for="safety_information" />
            </div>

            <div>
                <x-form.label for="references">References</x-form.label>
                <x-ui.textarea id="references" name="references" rows="4">{{ $oldValue('references') }}</x-ui.textarea>
                <x-form.error for="references" />
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
