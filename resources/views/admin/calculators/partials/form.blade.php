@props([
    'calculator' => null,
    'engines' => [],
    'statuses' => [],
    'action' => '',
    'method' => 'POST',
    'submitLabel' => 'Save Calculator',
])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-form.section
            title="Basic Information"
            description="Core details and publication status for this calculator."
        >
            <div>
                <x-form.label for="name" required>Name</x-form.label>
                <x-ui.input
                    id="name"
                    name="name"
                    value="{{ old('name', $calculator?->name) }}"
                    :error="$errors->first('name')"
                    aria-required="true"
                />
            </div>

            <div>
                <x-form.label for="slug">Slug</x-form.label>
                <x-ui.input
                    id="slug"
                    name="slug"
                    value="{{ old('slug', $calculator?->slug) }}"
                    :error="$errors->first('slug')"
                />
                <p class="mt-1 text-xs text-gray-500">Auto-generated from the name if left blank.</p>
            </div>

            <div>
                <x-form.label for="short_description">Short description</x-form.label>
                <x-ui.textarea id="short_description" name="short_description" rows="3">{{ old('short_description', $calculator?->short_description) }}</x-ui.textarea>
                <x-form.error for="short_description" />
            </div>

            <div>
                <x-form.label for="formula_display">Formula display</x-form.label>
                <x-ui.input
                    id="formula_display"
                    name="formula_display"
                    value="{{ old('formula_display', $calculator?->formula_display) }}"
                    placeholder="Sodium - (Chloride + Bicarbonate)"
                    :error="$errors->first('formula_display')"
                />
            </div>

            <div>
                <x-form.label for="status" required>Status</x-form.label>
                <x-ui.select id="status" name="status">
                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected(old('status', $calculator?->status?->value ?? 'draft') === $statusOption->value)>
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
            title="Calculator Configuration"
            description="Engine selection and input/output schemas. Schemas are JSON and are never executed."
        >
            <div>
                <x-form.label for="engine_reference" required>Engine</x-form.label>
                <x-ui.select id="engine_reference" name="engine_reference">
                    @foreach($engines as $reference => $label)
                        <option value="{{ $reference }}" @selected(old('engine_reference', $calculator?->engine_reference) === $reference)>
                            {{ $label }}
                        </option>
                    @endforeach
                </x-ui.select>
                <x-form.error for="engine_reference" />
            </div>

            <input type="hidden" name="engine" value="php_class">

            <div>
                <x-form.label for="input_schema" required>Input schema</x-form.label>
                <x-ui.textarea
                    id="input_schema"
                    name="input_schema"
                    rows="12"
                    class="font-mono text-xs"
                >{{ old('input_schema', $calculator ? json_encode($calculator->input_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</x-ui.textarea>
                <x-form.error for="input_schema" />
                <p class="mt-1 text-xs text-gray-500">
                    JSON array of fields. Each field requires key, label, and type (number, select, or text). Number fields may define unit, min, max, step, and required.
                </p>
            </div>

            <div>
                <x-form.label for="output_schema" required>Output schema</x-form.label>
                <x-ui.textarea
                    id="output_schema"
                    name="output_schema"
                    rows="8"
                    class="font-mono text-xs"
                >{{ old('output_schema', $calculator ? json_encode($calculator->output_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</x-ui.textarea>
                <x-form.error for="output_schema" />
                <p class="mt-1 text-xs text-gray-500">
                    JSON array of output fields. Each field requires key and label, and may define unit, rounding (none, round_up, round_down, round_half_up), and precision (0-10).
                </p>
            </div>

            <div>
                <x-form.label for="interpretation_notes">Interpretation notes</x-form.label>
                <x-ui.textarea id="interpretation_notes" name="interpretation_notes" rows="4">{{ old('interpretation_notes', $calculator?->interpretation_notes) }}</x-ui.textarea>
                <x-form.error for="interpretation_notes" />
            </div>

            <div>
                <x-form.label for="references">References</x-form.label>
                <x-ui.textarea id="references" name="references" rows="4">{{ old('references', $calculator?->references) }}</x-ui.textarea>
                <x-form.error for="references" />
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="Taxonomy" description="Organize this calculator by category and tag.">
            <div>
                <x-form.label>Categories</x-form.label>
                <x-admin.resources.category-selector
                    :selected="old('categories', $calculator?->categories->pluck('id')->all() ?? [])"
                />
                @error('categories')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-form.label>Tags</x-form.label>
                <x-admin.resources.tag-input
                    :selected="old('tags', $calculator?->tags->pluck('id')->all() ?? [])"
                />
                @error('tags')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-form.section>
    </x-ui.card>

    <x-ui.card>
        <x-form.section title="SEO" description="Optional search engine metadata for the public detail page.">
            <div>
                <x-form.label for="seo_title">SEO title</x-form.label>
                <x-ui.input
                    id="seo_title"
                    name="seo_title"
                    value="{{ old('seo_title', $calculator?->seo_title) }}"
                    :error="$errors->first('seo_title')"
                    maxlength="70"
                />
            </div>

            <div>
                <x-form.label for="seo_description">SEO description</x-form.label>
                <x-ui.textarea id="seo_description" name="seo_description" rows="3">{{ old('seo_description', $calculator?->seo_description) }}</x-ui.textarea>
                <x-form.error for="seo_description" />
            </div>
        </x-form.section>
    </x-ui.card>

    <div class="flex items-center justify-end gap-3">
        <x-ui.button href="{{ route('admin.calculators.index') }}" variant="ghost">
            Cancel
        </x-ui.button>
        <x-ui.button type="submit">
            {{ $submitLabel }}
        </x-ui.button>
    </div>
</form>
