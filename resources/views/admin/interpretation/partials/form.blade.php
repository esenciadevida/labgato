@props([
    'interpretation' => null,
    'statuses' => [],
    'action' => '',
    'method' => 'POST',
    'submitLabel' => 'Save Interpretation Resource',
    'showSaveAndCreate' => false,
])

@php
    $oldValue = fn (string $field) => old($field, $interpretation?->{$field});
    $authorNameDefault = $interpretation?->author_name ?? auth()->user()->name;
    $defaultDisclaimer = 'Educational Interpretation Only: This content is for educational purposes and explains common laboratory result patterns. It does not constitute medical advice or diagnosis. Always interpret laboratory results in the context of the patient\'s clinical condition and consult with a qualified healthcare professional.';
    $relatedAnalytes = old('related_analytes', $interpretation?->related_analytes ?? []);
    $relatedAnalytes = is_array($relatedAnalytes) ? implode(', ', $relatedAnalytes) : $relatedAnalytes;
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-form.section
            title="Basic Information"
            description="Core details and publication status for this interpretation resource."
        >
            <div>
                <x-form.label for="name" required>Name</x-form.label>
                <x-ui.input
                    id="name"
                    name="name"
                    value="{{ $oldValue('name') }}"
                    placeholder="Interpreting Elevated Liver Enzymes"
                    :error="$errors->first('name')"
                    aria-required="true"
                />
            </div>

            <div>
                <x-form.label for="slug">Slug</x-form.label>
                <x-ui.input
                    id="slug"
                    name="slug"
                    value="{{ $oldValue('slug') }}"
                    :error="$errors->first('slug')"
                />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Auto-generated from the name if left blank.</p>
            </div>

            <div>
                <x-form.label for="short_description">Short description</x-form.label>
                <x-ui.textarea id="short_description" name="short_description" rows="3">{{ $oldValue('short_description') }}</x-ui.textarea>
                <x-form.error for="short_description" />
            </div>

            <div>
                <x-form.label for="status" required>Status</x-form.label>
                <x-ui.select id="status" name="status">
                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected(old('status', $interpretation?->status?->value ?? 'draft') === $statusOption->value)>
                            {{ $statusOption->label() }}
                        </option>
                    @endforeach
                </x-ui.select>
                <x-form.error for="status" />
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Content"
            description="Write the body in Markdown. HTML is sanitized before rendering on the public site."
        >
            <div>
                <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
                    <x-form.label for="content" required>Content (Markdown)</x-form.label>
                    <x-ai.generate-button
                        use-case="draft_knowledge"
                        target="content"
                        label="Draft with AI"
                        title-field="name"
                        outline-field="content"
                        source-field="short_description"
                    />
                </div>
                <x-form.markdown-editor id="content" name="content" type="interpretation" rows="16" :error="$errors->first('content')" aria-required="true">{{ $oldValue('content') }}</x-form.markdown-editor>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Supports headings, bold, italics, links, lists, blockquotes, code blocks, and images. Paste or drag & drop an image to insert it.</p>
            </div>

            <div>
                <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
                    <x-form.label for="clinical_context">Clinical context</x-form.label>
                    <x-ai.generate-button
                        use-case="draft_clinical_context"
                        target="clinical_context"
                        label="Generate Context"
                        title-field="name"
                        source-field="content"
                    />
                </div>
                <x-ui.textarea id="clinical_context" name="clinical_context" rows="4">{{ $oldValue('clinical_context') }}</x-ui.textarea>
                <x-form.error for="clinical_context" />
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Disclaimer"
            description="Mandatory educational disclaimer shown prominently on the public detail page."
        >
            <div>
                <x-form.label for="mandatory_disclaimer" required>Mandatory disclaimer</x-form.label>
                <x-ui.textarea id="mandatory_disclaimer" name="mandatory_disclaimer" rows="4" :error="$errors->first('mandatory_disclaimer')" aria-required="true">{{ old('mandatory_disclaimer', $interpretation?->mandatory_disclaimer ?? $defaultDisclaimer) }}</x-ui.textarea>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Related Analytes & Metadata"
            description="Associated analytes, author, and estimated reading time."
        >
            <div>
                <x-form.label for="related_analytes">Related analytes</x-form.label>
                <x-ui.input
                    id="related_analytes"
                    name="related_analytes"
                    value="{{ $relatedAnalytes }}"
                    placeholder="ALT, AST, ALP, GGT"
                    :error="$errors->first('related_analytes')"
                />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Comma-separated list of related analyte names.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-form.label for="author_name">Author name</x-form.label>
                    <x-ui.input id="author_name" name="author_name" value="{{ old('author_name', $authorNameDefault) }}" :error="$errors->first('author_name')" />
                </div>

                <div>
                    <x-form.label for="reading_time_minutes">Reading time (minutes)</x-form.label>
                    <x-ui.input id="reading_time_minutes" name="reading_time_minutes" type="number" min="1" value="{{ $oldValue('reading_time_minutes') }}" :error="$errors->first('reading_time_minutes')" />
                </div>
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section
            title="Featured Image"
            description="Optional image displayed at the top of the public detail page. Max 2 MB (JPEG, PNG, WebP, or SVG)."
        >
            <div>
                @if($interpretation?->featured_image_url)
                    <img
                        src="{{ $interpretation->featured_image_url }}"
                        alt="Current featured image"
                        class="mb-3 max-h-40 w-auto rounded-lg border border-slate-200 dark:border-slate-700"
                        onerror="this.style.display='none'"
                    >
                @endif
                <x-ui.input id="featured_image" name="featured_image" type="file" accept=".jpg,.jpeg,.png,.webp" :error="$errors->first('featured_image')" />
                <x-form.error for="featured_image" />

                @if($interpretation?->featured_image)
                    <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="remove_featured_image" value="1" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                        Remove current image
                    </label>
                @endif
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Taxonomy" description="Organize this resource by category and tag.">
            <div>
                <x-form.label>Categories</x-form.label>
                <x-admin.resources.category-selector
                    :selected="old('categories', $interpretation?->categories->pluck('id')->all() ?? [])"
                />
                @error('categories')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-form.label>Tags</x-form.label>
                <x-admin.resources.tag-input
                    :selected="old('tags', $interpretation?->tags->pluck('id')->all() ?? [])"
                />
                @error('tags')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>
        </x-form.section>
    </x-ui.card>

    <x-admin.seo-eeat-fields :model="$interpretation" />

    <div class="flex items-center justify-end gap-3">
        <x-ui.button href="{{ route('admin.interpretation.index') }}" variant="ghost">
            Cancel
        </x-ui.button>
        @if($showSaveAndCreate)
            <x-ui.button type="submit" variant="secondary" name="save_action" value="save_and_create">
                Save and Create Another
            </x-ui.button>
        @endif
        <x-ui.button type="submit" name="save_action" value="save">
            {{ $submitLabel }}
        </x-ui.button>
    </div>
</form>
